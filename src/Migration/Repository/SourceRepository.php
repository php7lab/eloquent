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
        $files = FileHelper::scanDir($dir);
        $classes = [];
        foreach ($files as $file) {
            $classNameClean = FileHelper::fileRemoveExt($file);
            $entity = new MigrationEntity;
            $entity->className = 'Migrations\\' . $classNameClean;
            $entity->fileName = $dir . '\\' . $classNameClean . '.php';
            $entity->version = $classNameClean;
            include_once($entity->fileName);
            $classes[] = $entity;
        }
        return $classes;
    }

}