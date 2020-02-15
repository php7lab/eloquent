<?php

namespace PhpLab\Eloquent\Migration\Repositories;

use PhpLab\Core\Exceptions\InvalidConfigException;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Legacy\Yii\Helpers\FileHelper;
use PhpLab\Eloquent\Fixture\Traits\ConfigTrait;
use PhpLab\Eloquent\Migration\Entities\MigrationEntity;

class SourceRepository
{

    use ConfigTrait;

    public function __construct($mainConfigFile = null)
    {
        $config = $this->loadConfig($mainConfigFile);
        $this->config = $config['migrate'] ?? [];
        /*if(empty($this->config)) {
            throw new InvalidConfigException('Empty migrtion configuration!');
        }*/
    }

    public function getAll()
    {
        $directories = $this->config['directory'];
        if(empty($directories)) {
            throw new InvalidConfigException('Empty directories configuration for migrtion!');
        }
        $classes = [];
        foreach ($directories as $dir) {
            $newClasses = self::scanDir(FileHelper::prepareRootPath($dir));
            $classes = ArrayHelper::merge($classes, $newClasses);
        }
        return $classes;
    }

    private static function scanDir($dir)
    {
        $files = FileHelper::scanDir($dir);
        $classes = [];
        foreach ($files as $file) {
            $classNameClean = FileHelper::fileRemoveExt($file);
            $entity = new MigrationEntity;
            $entity->className = 'Migrations\\' . $classNameClean;
            $entity->fileName = $dir . DIRECTORY_SEPARATOR . $classNameClean . '.php';
            $entity->version = $classNameClean;
            include_once($entity->fileName);
            $classes[] = $entity;
        }
        return $classes;
    }

}