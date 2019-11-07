<?php

namespace PhpLab\Eloquent\Migration\Repository;

use PhpLab\Eloquent\Db\Enum\DbDriverEnum;
use PhpLab\Eloquent\Db\Helper\ManagerFactory;
use PhpLab\Eloquent\Db\Helper\TableAliasHelper;
use PhpLab\Eloquent\Migration\Entity\MigrationEntity;
use PhpLab\Eloquent\Migration\Base\BaseCreateTableMigration;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use php7extension\core\common\helpers\ClassHelper;
use php7extension\yii\helpers\ArrayHelper;
use php7extension\yii\helpers\FileHelper;

class HistoryRepository
{

    const MIGRATION_TABLE_NAME = 'eq_migration';

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

    private static function insert($version, $connectionName = 'default') {
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $queryBuilder = Manager::table($targetTableName, null, $connectionName);
        $queryBuilder->insert([
            'version' => $version,
            'executed_at' => new \DateTime(),
        ]);
    }

    private static function delete($version, $connectionName = 'default') {
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $queryBuilder = Manager::table($targetTableName, null, $connectionName);
        $queryBuilder->where('version', $version);
        $queryBuilder->delete();
    }

    public static function upMigration($class) {
        /** @var BaseCreateTableMigration $migration */
        $migration = new $class;
        $schema = Manager::schema($migration->connectionName());
        $connection = $schema->getConnection();
        // todo: begin transaction
        $connection->beginTransaction();
        $migration->up($schema);
        $version = ClassHelper::getClassOfClassName($class);
        self::insert($version);
        $connection->commit();
        // todo: end transaction
    }

    public static function downMigration($class) {
        /** @var BaseCreateTableMigration $migration */
        $migration = new $class;
        $schema = Manager::schema($migration->connectionName());
        $connection = $schema->getConnection();
        // todo: begin transaction
        $connection->beginTransaction();
        $migration->down($schema);
        $version = ClassHelper::getClassOfClassName($class);
        self::delete($version);
        $connection->commit();
        // todo: end transaction
    }

    public static function all($connectionName = 'default') {
        self::forgeMigrationTable($connectionName);
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $queryBuilder = Manager::table($targetTableName, null, $connectionName);
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

    private static function forgeMigrationTable($connectionName = 'default') {
        ManagerFactory::forgeDb($connectionName);
        $schema = Manager::schema($connectionName);
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $hasTable = $schema->hasTable($targetTableName);
        if($hasTable) {
            return;
        }
        self::createMigrationTable($connectionName);
    }

    private static function createMigrationTable($connectionName = 'default') {
        $tableSchema = function (Blueprint $table) {
            $table->string('version')->primary();
            $table->timestamp('executed_at');
        };
        $schema = Manager::schema($connectionName);
        $targetTableName = TableAliasHelper::encode($connectionName, self::MIGRATION_TABLE_NAME);
        $schema->create($targetTableName, $tableSchema);
    }

}