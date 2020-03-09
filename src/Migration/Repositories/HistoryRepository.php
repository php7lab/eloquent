<?php

namespace PhpLab\Eloquent\Migration\Repositories;

use Illuminate\Database\Schema\Blueprint;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Eloquent\Db\Base\BaseEloquentRepository;
use PhpLab\Eloquent\Migration\Base\BaseCreateTableMigration;
use PhpLab\Eloquent\Migration\Entities\MigrationEntity;
use PhpLab\Core\Helpers\ClassHelper;

//use PhpLab\Eloquent\Db\Helpers\TableAliasHelper;

class HistoryRepository extends BaseEloquentRepository
{

    const MIGRATION_TABLE_NAME = 'eq_migration';

    protected $tableName = self::MIGRATION_TABLE_NAME;

    public function getEntityClass(): string
    {
        return MigrationEntity::class;
    }

    public static function filterVersion(array $sourceCollection, array $historyCollection)
    {
        /**
         * @var MigrationEntity[] $historyCollection
         * @var MigrationEntity[] $sourceCollection
         */

        $sourceVersionArray = ArrayHelper::getColumn($sourceCollection, 'version');
        $historyVersionArray = ArrayHelper::getColumn($historyCollection, 'version');

        $diff = array_diff($sourceVersionArray, $historyVersionArray);

        foreach ($sourceCollection as $key => $migrationEntity) {
            if ( ! in_array($migrationEntity->version, $diff)) {
                unset($sourceCollection[$key]);
            }
        }
        return $sourceCollection;
    }

    private function insert($version, $connectionName = 'default')
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode($connectionName, self::MIGRATION_TABLE_NAME);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insert([
            'version' => $version,
            'executed_at' => new \DateTime(),
        ]);
    }

    private function delete($version, $connectionName = 'default')
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode($connectionName, self::MIGRATION_TABLE_NAME);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where('version', $version);
        $queryBuilder->delete();
    }

    public function upMigration($class)
    {
        /** @var BaseCreateTableMigration $migration */
        $migration = new $class($this->getCapsule());
        $schema = $this->getSchema();
        $connection = $schema->getConnection();
        // todo: begin transaction
        $connection->beginTransaction();
        $migration->up($schema);
        $version = ClassHelper::getClassOfClassName($class);
        $this->insert($version);
        $connection->commit();
        // todo: end transaction
    }

    public function downMigration($class)
    {
        /** @var BaseCreateTableMigration $migration */
        $migration = new $class($this->getCapsule());
        $schema = $this->getSchema();
        $connection = $schema->getConnection();
        // todo: begin transaction
        $connection->beginTransaction();
        $migration->down($schema);
        $version = ClassHelper::getClassOfClassName($class);
        self::delete($version);
        $connection->commit();
        // todo: end transaction
    }

    public function all($connectionName = 'default')
    {
        $this->forgeMigrationTable($connectionName);
        $queryBuilder = $this->getQueryBuilder();
        $array = $queryBuilder->get()->toArray();
        $collection = [];
        foreach ($array as $item) {
            $entityClass = $this->getEntityClass();
            $entity = new $entityClass;
            $entity->version = $item->version;
            //$entity->className = $className;
            $collection[] = $entity;
        }
        return $collection;
    }

    private function forgeMigrationTable($connectionName = 'default')
    {
        $schema = $this->getSchema($connectionName);
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode($connectionName, self::MIGRATION_TABLE_NAME);
        $hasTable = $schema->hasTable($targetTableName);
        if ($hasTable) {
            return;
        }
        $this->createMigrationTable($connectionName);
    }

    private function createMigrationTable($connectionName = 'default')
    {
        $tableSchema = function (Blueprint $table) {
            $table->string('version')->primary();
            $table->timestamp('executed_at');
        };
        $schema = $this->getSchema($connectionName);
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode($connectionName, self::MIGRATION_TABLE_NAME);
        $schema->create($targetTableName, $tableSchema);
    }

}