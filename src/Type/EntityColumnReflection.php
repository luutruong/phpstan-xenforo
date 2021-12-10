<?php declare(strict_types=1);

namespace App\Type;

use XF\Mvc\Entity\Entity;
use PHP\Type\MixedType;

class EntityColumnReflection implements \PHPStan\Reflection\PropertyReflection
{
    protected $classReflection;
    protected $type;
    protected $config;

    public function __construct($classReflection, int $type, array $config = [])
    {
        $this->classReflection = $classReflection;
        $this->type = $type;
        $this->config = $config;
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
        switch ($this->type) {
            case Entity::INT:
                $min = $this->config['min'] ?? null;
                $max = $this->config['max'] ?? null;
                if ($min !== null || $max !== null) {
                    return new \PHPStan\Type\IntegerRangeType($min, $max);
                }

                return new \PHPStan\Type\IntegerType();
            case Entity::UINT:
                return new \PHPStan\Type\IntegerRangeType(0, $this->config['max'] ?? null);
            case Entity::FLOAT:
                return new \PHPStan\Type\FloatType();
            case Entity::BOOL:
                return new \PHPStan\Type\BooleanType();
            case Entity::STR:
            case Entity::JSON:
                return new \PHPStan\Type\StringType();
            case Entity::BINARY:
            case Entity::SERIALIZED:
                return new \PHPStan\Type\MixedType();
            case Entity::JSON_ARRAY:
            case Entity::LIST_COMMA:
            case Entity::LIST_LINES:
            case Entity::LIST_ARRAY:
                return new \PHPStan\Type\ArrayType(
                    new \PHPStan\Type\MixedType(),
                    new \PHPStan\Type\MixedType()
                );
        }

        throw new \PHPStan\ShouldNotHappenException();
    }

    public function getWritableType(): \PHPStan\Type\Type
    {
        return $this->getReadableType();
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
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
