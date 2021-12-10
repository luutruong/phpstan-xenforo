<?php declare(strict_types=1);

namespace App\Type;

class EntityGetterReflection implements \PHPStan\Reflection\PropertyReflection
{
    protected $classReflection;
    protected $type;
    protected $writable;

    public function __construct($classReflection, \PHPStan\Type\Type $type, bool $writable)
    {
        $this->classReflection = $classReflection;
        $this->type = $type;
        $this->writable = $writable;
    }

    public function getDeclaringClass(): \PHPStan\Reflection\ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getReadableType(): \PHPStan\Type\Type
    {
        return $this->type;
    }

    public function getWritableType(): \PHPStan\Type\Type
    {
        return $this->getReadableType();
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function isDeprecated(): \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isInternal(): \PHPStan\TrinaryLogic
    {
        return \PHPStan\TrinaryLogic::createYes();
    }
}
