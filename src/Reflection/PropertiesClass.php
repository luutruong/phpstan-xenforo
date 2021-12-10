<?php declare(strict_types=1);

namespace App\Reflection;

use App\Type\EntityColumnReflection;
use App\Type\EntityGetterReflection;
use App\Type\EntityRelationReflection;
use XF\Mvc\Entity\Structure;

class PropertiesClass
{
    /**
     * @var array
     */
    protected static $xfStructures = [];

    public function hasProperty($classReflection, $propertyName)
    {
        $structure = $this->getStructure($classReflection);
        if ($structure !== null) {
            if (substr($propertyName, -1, 1) === '_') {
                $propertyName = substr($propertyName, 0, -1);
            }

            if (isset($structure->columns[$propertyName])) {
                return true;
            } elseif (isset($structure->getters[$propertyName])) {
                return true;
            } elseif (isset($structure->relations[$propertyName])) {
                return true;
            }
        }

        return false;
    }

    public function getProperty($classReflection, string $propertyName)
    {
        $structure = $this->getStructure($classReflection);
        if ($structure === null) {
            throw new \PHPStan\ShouldNotHappenException();
        }

        $useGetter = true;
        if (substr($propertyName, -1, 1) === '_') {
            $useGetter = false;
            $propertyName = substr($propertyName, 0, -1);
        }

        if (isset($structure->getters[$propertyName]) && $useGetter) {
            $getter = $structure->getters[$propertyName];
            if (is_array($getter) && isset($getter['getter']) && is_string($getter['getter'])) {
                $method = $getter['getter'];
            } else {
                $method = 'get' . \XF\Util\Php::camelCase($propertyName);
            }

            if ($classReflection->hasNativeMethod($method)) {
                $nativeMethod = $classReflection->getNativeMethod($method);

                return new EntityGetterReflection(
                    $classReflection,
                    $nativeMethod->getVariants()[0]->getNativeReturnType(),
                    isset($structure->columns[$propertyName])
                );
            }

            throw new \PHPStan\ShouldNotHappenException(sprintf(
                'Getter \'%s\' does not exists',
                $method
            ));
        }

        if (isset($structure->columns[$propertyName])) {
            return new EntityColumnReflection(
                $classReflection,
                $structure->columns[$propertyName]['type'],
                $structure->columns[$propertyName]
            );
        }

        if (isset($structure->relations[$propertyName])) {
            return new EntityRelationReflection(
                $classReflection,
                $structure->relations[$propertyName]['type'],
                $structure->relations[$propertyName]['entity']
            );
        }

        throw new \PHPStan\ShouldNotHappenException();
    }

    public function getStructure($classReflection): ?Structure
    {
        if ($classReflection->isSubclassOf('XF\Mvc\Entity\Entity')) {
            if (isset(static::$xfStructures[$classReflection->getName()])) {
                return static::$xfStructures[$classReflection->getName()];
            }

            try {
                $structure = new Structure();
                call_user_func([$classReflection->getName(), 'getStructure'], $structure);
                static::$xfStructures[$classReflection->getName()] = $structure;

                return $structure;
            } catch (\Throwable $e) {
            }
        }

        return null;
    }
}
