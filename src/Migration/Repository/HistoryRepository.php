<?php

namespace PhpLab\Eloquent\Migration\Repository;

use PhpLab\Eloquent\Db\Enum\DbDriverEnum;
use PhpLab\Eloquent\Db\Helper\ManagerFactory;
use PhpLab\Eloquent\Db\Helper\TableAliasHelper;
use PhpLab\Eloquent\Db\Repository\BaseDbRepository;
use PhpLab\Eloquent\Migration\Entity\MigrationEntity;
use PhpLab\Eloquent\Migration\Base\BaseCreateTableMigration;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use php7extension\core\common\helpers\ClassHelper;
use php7extension\yii\helpers\ArrayHelper;
use php7extension\yii\helpers\FileHelper;

class HistoryRepository extends BaseDbRepository
{

    const MIGRATION_TABLE_NAME = 'eq_migration';

    protected $tableName = self::MIGRATION_TABLE_NAME;

    public static function filterVersion(array $sourceCollection, array $historyCollection) {
        /**
         * @var MigrationEntity[] $historyCollection
         * @var MigrationEntity[] $sourceCollection
         */

        $sourceVersionArray = ArrayHelper::getColumn($sourceCollection, 'version');
        $historyVersionArray = ArrayHelper::getColumn($historyCollection, 'version');

        $diff = array_diff($sourceVersionArray, $historyVersionArray);

        foreach ($sourceCollection as $key => $migrationEntity) {
            if( ! in_array($migrationEntity->version, $diff)) {
                unset($sourceCollection[$key]);
            }
        }
        return $sourceCollection;
    }

    private function insert($version, $connectionName = 'default') {
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        //$queryBuilder = $this->getQueryBuilder();
        //$queryBuilder = Manager::table($targetTableName, null, $connectionName);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->insert([
            'version' => $version,
            'executed_at' => new \DateTime(),
        ]);
    }

    private function delete($version, $connectionName = 'default') {
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        //$queryBuilder = Manager::table($targetTableName, null, $connectionName);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where('version', $version);
        $queryBuilder->delete();
    }

    public function upMigration($class) {
        /** @var BaseCreateTableMigration $migration */
        $migration = new $class;
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

    public function downMigration($class) {
        /** @var BaseCreateTableMigration $migration */
        $migration = new $class;
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

    public function all($connectionName = 'default') {
        $this->forgeMigrationTable($connectionName);
        //$targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        //$queryBuilder = Manager::table($targetTableName, null, $connectionName);
        $queryBuilder = $this->getQueryBuilder();
        $array = $queryBuilder->get()->toArray();
        $collection = [];
        foreach ($array as $item) {
            $entity = new MigrationEntity;
            $entity->version = $item->version;
            //$entity->className = $className;
            $collection[] = $entity;
        }
        return $collection;
    }

    private function forgeMigrationTable($connectionName = 'default') {
        //ManagerFactory::forgeDb($connectionName);
        $schema = $this->getSchema($connectionName);
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $hasTable = $schema->hasTable($targetTableName);
        if($hasTable) {
            return;
        }
        $this->createMigrationTable($connectionName);
    }

    private function createMigrationTable($connectionName = 'default') {
        $tableSchema = function (Blueprint $table) {
            $table->string('version')->primary();
            $table->timestamp('executed_at');
        };
        $schema = $this->getSchema($connectionName);
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $schema->create($targetTableName, $tableSchema);
    }

}