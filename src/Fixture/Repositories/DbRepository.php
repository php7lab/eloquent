<?php

namespace PhpLab\Eloquent\Fixture\Repositories;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;
use php7extension\yii\helpers\ArrayHelper;
use Illuminate\Support\Collection;
use PhpLab\Eloquent\Db\Enums\DbDriverEnum;
use PhpLab\Eloquent\Db\Repositories\BaseEloquentRepository;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;

class DbRepository extends BaseEloquentRepository
{

    public $entityClass = FixtureEntity::class;

    public function __construct(\PhpLab\Eloquent\Db\Helpers\Manager $capsule)
    {
        parent::__construct($capsule);

        $schema = $this->getSchema();

        // Выключаем проверку целостности связей
        $schema->disableForeignKeyConstraints();
    }

    public function dropAllTables()
    {
        $schema = $this->getSchema();
        $schema->dropAllTables();
    }
    
    public function dropAllViews()
    {
        $schema = $this->getSchema();
        $schema->dropAllViews();
    }

    public function dropAllTypes()
    {
        $schema = $this->getSchema();
        $schema->dropAllTypes();
    }
    
    public function deleteTable($name)
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $schema = $this->getSchema();
        $schema->drop($targetTableName);
    }

    public function saveData($name, Collection $collection)
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($targetTableName);
        $queryBuilder->truncate();
        $data = ArrayHelper::toArray($collection);
        $queryBuilder->insert($data);
        $this->resetAutoIncrement($name);
    }

    public function loadData($name): Collection
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($targetTableName);
        $data = $queryBuilder->get()->toArray();
        return new Collection($data);
    }

    public function allTables(): Collection
    {
        $tableAlias = $this->getCapsule()->getAlias();
        /* @var Builder|MySqlBuilder|PostgresBuilder $schema */
        $schema = $this->getSchema();
        $dbName = $schema->getConnection()->getDatabaseName();
        $array = $schema->getAllTables();
        $collection = new Collection;
        foreach ($array as $item) {
            $key = 'Tables_in_' . $dbName;
            $targetTableName = $item->{$key};
            $sourceTableName = $tableAlias->decode('default', $targetTableName);
            $entity = $this->forgeEntity([
                'name' => $sourceTableName,
            ]);
            $collection->add($entity);
        }
        return $collection;
    }

    private function resetAutoIncrement($name)
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($targetTableName);
        $driver = $this->getConnection()->getConfig('driver');
        if ($driver == DbDriverEnum::PGSQL) {
            $max = $queryBuilder->max('id');
            if ($max) {
                $pkName = 'id';
                $sql = 'SELECT setval(\'' . $targetTableName . '_' . $pkName . '_seq\', ' . ($max + 1) . ')';
                $connection = $queryBuilder->getConnection();
                $connection->statement($sql);
            }
        }
    }

}