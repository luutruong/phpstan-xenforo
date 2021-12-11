<?php

namespace App\Node;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

class Arg
{
    protected $arg;

    public function __construct(\PhpParser\Node\Arg $arg)
    {
        $this->arg = $arg;
    }

    protected function concat(Concat $concat): string
    {
        $value = '';
        if ($concat->left instanceof String_) {
            $value = $concat->left->value;
        } elseif ($concat->left instanceof Concat) {
            $value = $this->concat($concat->left);
        } elseif ($concat->left instanceof Variable) {
            $value = '$' . $concat->left->name;
        }

        if ($concat->right instanceof String_) {
            $value .= $concat->right->value;
        } elseif ($concat->right instanceof Concat) {
            $value .= $this->concat($concat->right);
        } elseif ($concat->right instanceof Variable) {
            $value .= '$' . $concat->right->name;
        }

        return $value;
    }

    public function __toString(): string
    {
        $value = $this->arg->value;
        if ($value instanceof String_) {
            return $value->value;
        } elseif ($value instanceof Concat) {
            return $this->concat($value);
        } elseif ($value instanceof Array_) {
            return 'Array';
        }

        return '';
    }
}
