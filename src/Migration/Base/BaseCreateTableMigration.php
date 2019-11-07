<?php

namespace PhpLab\Eloquent\Migration\Base;

use PhpLab\Eloquent\Db\Enum\DbDriverEnum;
use \Illuminate\Database\Schema\Builder;

abstract class BaseCreateTableMigration extends BaseMigration
{

    protected $tableComment = '';

    abstract public function tableSchema();

    public function up(Builder $schema)
    {
        $schema->create($this->tableName(), $this->tableSchema());
        if($this->tableComment) {
            $this->addTableComment($schema);
        }
    }

    public function down(Builder $schema)
    {
        $schema->dropIfExists($this->tableName());
    }

    private function addTableComment(Builder $schema) {
        $connection = $schema->getConnection();
        $driver = $connection->getConfig('driver');
        $table = $this->tableName();
        $tableComment = $this->tableComment;
        $sql = '';
        if($driver == DbDriverEnum::MYSQL) {
            $sql = "ALTER TABLE {$table} COMMENT = '{$tableComment}';";
        }
        if($driver == DbDriverEnum::PGSQL) {
            $sql = "COMMENT ON TABLE {$table} IS '{$tableComment}';";
        }
        if($sql) {
            $this->runSqlQuery($schema, $sql);
        }
    }

}