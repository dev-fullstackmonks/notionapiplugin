<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2c486a9d6203e16e09930ec0ff0ff045
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LWM\\Disc\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LWM\\Disc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2c486a9d6203e16e09930ec0ff0ff045::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2c486a9d6203e16e09930ec0ff0ff045::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
