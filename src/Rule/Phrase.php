<?php

namespace App\Rule;

use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

class Phrase implements \PHPStan\Rules\Rule
{
    protected $normalized = [];
    protected $phrases = [];

    public function getNodeType(): string
    {
        return \PhpParser\Node\Expr\StaticCall::class;
    }

    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
    {
        if (!$node->class instanceof \PhpParser\Node\Name) {
            return [];
        }

        $className = $node->class->toString();
        if ($className !== 'XF') {
            return [];
        }

        $methodName = $node->name->toString();
        if ($methodName !== 'phrase' && $methodName !== 'phraseDeferred') {
            return [];
        }

        $next = $node->name->getAttribute('next');
        if (!$next instanceof \PhpParser\Node\Arg) {
            throw new ShouldNotHappenException('Unknown next node name: ' . get_class($next));
        }

        $phraseId = $this->processArg($next);
        if ($next->value instanceof String_) {
            $phraseId = $this->normalizePhrase($phraseId);
            $phrase = $this->fetchPhrase($phraseId);

            if ($phrase !== null) {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message("Phrase $phraseId could not be found.")->build()
        ];
    }

    protected function fetchPhrase(string $title): ?\XF\Entity\Phrase
    {
        if (array_key_exists($title, $this->phrases)) {
            return $this->phrases[$title];
        }

        /** @var \XF\Finder\Phrase $finder */
        $finder = \XF::finder('XF:Phrase');
        $finder->where('title', $title);
        $finder->where('language_id', 0);

        /** @var \XF\Entity\Phrase|null $phrase */
        $phrase = $finder->fetchOne();
        $this->phrases[$title] = $phrase;

        return $phrase;
    }

    protected function normalizePhrase(string $id): string
    {
        if (array_key_exists($id, $this->normalized)) {
            return $this->normalized[$id];
        }

        $language = \XF::app()->language();
        $reflection = new \ReflectionClass($language);
        $method = $reflection->getMethod('getEffectivePhraseName');
        $method->setAccessible(true);

        $normalized = $method->invokeArgs($language, [
            $id
        ]);
        $this->normalized[$id] = $normalized;

        return $this->normalized[$id];
    }

    protected function processArg(\PhpParser\Node\Arg $arg): string
    {
        if ($arg->value instanceof Concat) {
            return $this->nodeToString($arg->value->left) . $this->nodeToString($arg->value->right);
        } elseif ($arg->value instanceof PropertyFetch) {
            return $this->nodeToString($arg->value->var) . '->' . $this->nodeToString($arg->value->name);
        }

        return $this->nodeToString($arg->value);
    }

    protected function nodeToString($node): string
    {
        if (is_string($node)) {
            return $node;
        }

        if ($node instanceof String_) {
            return $node->value;
        } elseif ($node instanceof Variable) {
            return '$' . $node->name;
        } elseif ($node instanceof ClassConstFetch) {
            return $node->class . '::' . $node->name;
        } elseif ($node instanceof ConstFetch) {
            return $node->name;
        } elseif ($node instanceof PropertyFetch) {
            return $this->nodeToString($node->var) . '->' . $this->nodeToString($node->name);
        } elseif ($node instanceof MethodCall) {
            return sprintf(
                '%s->%s(%s)',
                $this->nodeToString($node->var),
                $this->nodeToString($node->name),
                count($node->args) > 0 ? '...' : ''
            );
        } elseif ($node instanceof FuncCall) {
            return sprintf(
                '%s(%s)',
                $this->nodeToString($node->name),
                count($node->args) > 0 ? '...' : ''
            );
        } elseif ($node instanceof Identifier) {
            return $this->nodeToString($node->name);
        } elseif ($node instanceof Name) {
            return implode('', $node->parts);
        }

        return 'Unknown nodeToString(' . get_class($node) . ')';
    }
}
