<?php

namespace PhpLab\Eloquent\Fixture\Repositories;

use Illuminate\Support\Collection;
use PhpLab\Core\Domain\Traits\ForgeEntityTrait;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Legacy\Yii\Helpers\FileHelper;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;
use PhpLab\Eloquent\Fixture\Traits\ConfigTrait;
use PhpLab\Core\Libs\Store\StoreFile;

class FileRepository
{

    use ConfigTrait;
    use ForgeEntityTrait;

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