<?php

namespace PhpLab\Eloquent\Fixture\Repository;

use php7extension\core\store\StoreFile;
use php7extension\yii\helpers\ArrayHelper;
use php7extension\yii\helpers\FileHelper;
use PhpLab\Domain\Data\Collection;
use PhpLab\Domain\Repository\BaseRepository;
use PhpLab\Eloquent\Fixture\Entity\FixtureEntity;
use PhpLab\Eloquent\Fixture\Traits\ConfigTrait;

class FileRepository extends BaseRepository
{

    use ConfigTrait;

    public $entityClass = FixtureEntity::class;
    public $extension = 'php';

    public function __construct($mainConfigFile = null)
    {
        $config = $this->loadConfig($mainConfigFile);
        $this->config = $config['fixture'];
    }

    public function allTables(): Collection
    {
        $array = [];
        foreach ($this->config['directory'] as $dir) {
            $fixtureArray = $this->scanDir(FileHelper::prepareRootPath($dir));
            $array = ArrayHelper::merge($array, $fixtureArray);
        }
        $collection = $this->forgeEntityCollection($array);
        return $collection;
    }

    public function saveData($name, Collection $collection)
    {
        $data = ArrayHelper::toArray($collection);
        $this->getStoreInstance($name)->save($data);
    }

    public function loadData($name): Collection
    {
        $data = $this->getStoreInstance($name)->load();
        return new Collection($data);
    }

    private function oneByName(string $name): FixtureEntity
    {
        $collection = $this->allTables();
        $collection = $collection->where('name', '=', $name);
        if ($collection->count() < 1) {
            return $this->forgeEntity([
                'name' => $name,
                'fileName' => $this->config['directory']['default'] . '/' . $name . '.' . $this->extension,
            ]);
        }

        return $this->forgeEntity($collection->first());
    }

    private function getStoreInstance(string $name): StoreFile
    {
        $entity = $this->oneByName($name);
        $store = new StoreFile($entity->fileName);
        return $store;
    }

    private function scanDir($dir): array
    {
        $files = FileHelper::scanDir($dir);
        $array = [];
        foreach ($files as $file) {
            $name = FileHelper::fileRemoveExt($file);
            $entity = [
                'name' => $name,
                'fileName' => $dir . '/' . $file,
            ];
            $array[] = $entity;
        }
        return $array;
    }

}