<?php

namespace PhpLab\Eloquent\Fixture\Repositories;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;
use Illuminate\Support\Collection;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Eloquent\Db\Enums\DbDriverEnum;
use PhpLab\Eloquent\Db\Base\BaseEloquentRepository;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;

class DbRepository extends BaseEloquentRepository
{

    public function getEntityClass(): string
    {
        return FixtureEntity::class;
    }

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

    public function truncateData($name)
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($targetTableName);
        $queryBuilder->truncate();
    }

    public function saveData($name, Collection $collection)
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode('default', $name);
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($targetTableName);
        //$queryBuilder->truncate();
        $chunks = $collection->chunk(150);
        foreach ($chunks as $chunk) {
            $data = ArrayHelper::toArray($chunk);
            $queryBuilder->insert($data);
        }
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
        $collection = new Collection;
        if($schema->getConnection()->getDriverName() == DbDriverEnum::SQLITE) {
            $array = $schema->getConnection()->getPdo()->query('SELECT name FROM sqlite_master WHERE type=\'table\'')->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($array as $targetTableName) {
                $sourceTableName = $tableAlias->decode('default', $targetTableName);
                $entityClass = $this->getEntityClass();
                $entity = EntityHelper::createEntity($entityClass, [
                    'name' => $sourceTableName,
                ]);
                $collection->add($entity);
            }
        } else {
            $array = $schema->getAllTables();
            foreach ($array as $item) {
                $key = 'Tables_in_' . $dbName;
                $targetTableName = $item->{$key};
                $sourceTableName = $tableAlias->decode('default', $targetTableName);
                $entityClass = $this->getEntityClass();
                $entity = EntityHelper::createEntity($entityClass, [
                    'name' => $sourceTableName,
                ]);
                $collection->add($entity);
            }
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

        /* @var Builder|MySqlBuilder|PostgresBuilder $schema */
        $schema = $this->getSchema();

        if ($driver == DbDriverEnum::PGSQL && $schema->hasColumn($name, 'id')) {
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