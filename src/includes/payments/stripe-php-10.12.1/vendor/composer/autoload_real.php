<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInita59d75286e6705b2448c29cfdc2d14dd
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInita59d75286e6705b2448c29cfdc2d14dd', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInita59d75286e6705b2448c29cfdc2d14dd', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInita59d75286e6705b2448c29cfdc2d14dd::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}