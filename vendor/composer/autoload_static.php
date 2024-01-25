<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita74cb7e29609a8e70a9bdfef9d95559f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PhpOffice\\PhpWord\\' => 18,
        ),
        'L' => 
        array (
            'Laminas\\Escaper\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PhpOffice\\PhpWord\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/phpword/src/PhpWord',
        ),
        'Laminas\\Escaper\\' => 
        array (
            0 => __DIR__ . '/..' . '/laminas/laminas-escaper/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita74cb7e29609a8e70a9bdfef9d95559f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita74cb7e29609a8e70a9bdfef9d95559f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita74cb7e29609a8e70a9bdfef9d95559f::$classMap;

        }, null, ClassLoader::class);
    }
}
