<?php

namespace App\Node;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;

class XFNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    protected $phrases = [];

    /**
     * @return array
     */
    public function getPhrases(): array
    {
        return $this->phrases;
    }

    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof StaticCall) {
            $className = $node->class->parts[0];
            $method = $node->name->name;
            if ("$className::$method" === 'XF::phrase') {
                if (count($node->args) === 0) {
                    throw new \RuntimeException('Use XF::phrase without an argument!');
                }

                foreach ($node->args as $arg) {
                    $arg_ = new Arg($arg);
                    $rendered = $arg_->__toString();
                    if ($rendered === 'Array') {
                        continue;
                    } elseif ($rendered === '') {
                        throw new \RuntimeException('Unknown arg type');
                    } else {
                        $this->phrases[] = [
                            'id' => $rendered,
                            'startLine' => $arg->getAttribute('startLine'),
                            'endLine' => $arg->getAttribute('endLine')
                        ];
                    }
                }
            }
        }

        return parent::enterNode($node);
    }
}
