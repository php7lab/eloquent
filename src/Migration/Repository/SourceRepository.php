<?php

namespace PhpLab\Eloquent\Migration\Repository;

use PhpLab\Eloquent\Db\Helper\ManagerFactory;
use PhpLab\Eloquent\Migration\Entity\MigrationEntity;
use php7extension\yii\helpers\ArrayHelper;
use php7extension\yii\helpers\FileHelper;

class SourceRepository
{

    public static function getAll()
    {
        $config = ManagerFactory::getConfig(ManagerFactory::MIGRATE);
        $directories = $config['directory'];
        $classes = [];
        foreach ($directories as $dir) {
            $newClasses = self::scanDir($dir);
            $classes = ArrayHelper::merge($classes, $newClasses);
        }
        return $classes;
    }

    private static function getRootPath()
    {
        $rootDir = __DIR__ . '/../../../../../';
        return $rootDir;
    }

    private static function scanDir($dir)
    {
        $srcDir = self::getRootPath() . 'src/';
        $files = FileHelper::scanDir($srcDir . $dir);
        $classes = [];
        foreach ($files as $file) {
            $classNameClean = FileHelper::fileRemoveExt($file);
            $className = 'App\\' . $dir . '\\' . $classNameClean;
            //$classes[$classNameClean] = $className;

            $entity = new MigrationEntity;
            $entity->version = $classNameClean;
            $entity->className = $className;
            $classes[] = $entity;
        }
        return $classes;
    }

}