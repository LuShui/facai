<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
            'think\\' => 6,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'J' => 
        array (
            'JMessage\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'think\\' => 
        array (
            0 => __DIR__ . '/../..' . '/thinkphp/library/think',
            1 => __DIR__ . '/..' . '/topthink/think-image/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
        'JMessage\\' => 
        array (
            0 => __DIR__ . '/..' . '/jiguang/jmessage/src/JMessage',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2bc4f313dba415539e266f7ac2c87dcd::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
