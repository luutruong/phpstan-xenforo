<?php

if (!isset($_SERVER['PHPSTAN_XENFORO_ROOT_DIR'])) {
    throw new \RuntimeException('`PHPSTAN_XENFORO_ROOT_DIR` not exists');
}
$xfDir = $_SERVER['PHPSTAN_XENFORO_ROOT_DIR'];
include $xfDir . '/src/XF.php';

$appClass = 'XF\Pub\App';

\XF::start($xfDir);
\XF::setupApp($appClass);

stream_wrapper_restore('phar');

spl_autoload_register(function (string $className) {
    if (strpos($className, 'XFCP_') === false) {
        return null;
    }

    static $classExtensions = null;
    if ($classExtensions === null) {
        $extension = \XF::app()->extension();
        $reflection = new ReflectionClass($extension);
        $property = $reflection->getProperty('classExtensions');
        $property->setAccessible(true);

        $classExtensions = $property->getValue($extension);
    }

    $classWithoutXFCP = str_replace('XFCP_', '', $className);
    foreach ($classExtensions as $classBase => $classes) {
        foreach ($classes as $classExtended) {
            if ($classExtended === $classWithoutXFCP) {
                class_alias($classBase, $className);

                return true;
            }
        }
    }

    return null;
});

return \XF::$autoLoader;
