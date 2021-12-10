<?php declare(strict_types=1);

namespace App\Type;

use XF\Mvc\Entity\Entity;

class EntityRelationReflection implements \PHPStan\Reflection\PropertyReflection
{
    protected $classReflection;
    protected $type;
    protected $entity;

    public function __construct($classReflection, int $type, string $entity)
    {
        $this->classReflection = $classReflection;
        $this->type = $type;
        $this->entity = $entity;
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
        $entityClass = \XF::stringToClass($this->entity, '%s\Entity\%s');
        if ($this->type === Entity::TO_ONE) {
            return new \PHPStan\Type\UnionType([
                new \PHPStan\Type\NullType(),
                new \PHPStan\Type\ObjectType($entityClass)
            ]);
        }

        return new \PHPStan\Type\UnionType([
            new \PHPStan\Type\ObjectType('XF\Mvc\Entity\AbstractCollection'),
            new \PHPStan\Type\ObjectType($entityClass)
        ]);
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
        return false;
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
