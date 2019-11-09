<?php

namespace PhpLab\Eloquent\Migration\Base;

use Illuminate\Database\Schema\Builder;
use PhpLab\Eloquent\Db\Enum\DbDriverEnum;
use PhpLab\Eloquent\Db\Helper\Manager;

abstract class BaseCreateTableMigration extends BaseMigration
{

    protected $tableComment = '';
    protected $capsule;

    abstract public function tableSchema();

    public function __construct(Manager $capsule)
    {
        $this->capsule = $capsule;
    }

    public function getCapsule(): Manager
    {
        return $this->capsule;
    }

    public function up(Builder $schema)
    {
        $schema->create($this->tableName(), $this->tableSchema());
        if ($this->tableComment) {
            $this->addTableComment($schema);
        }
    }

    public function down(Builder $schema)
    {
        $schema->dropIfExists($this->tableName());
    }

    private function addTableComment(Builder $schema)
    {
        $connection = $schema->getConnection();
        $driver = $connection->getConfig('driver');
        $table = $this->tableName();
        $tableComment = $this->tableComment;
        $sql = '';
        if ($driver == DbDriverEnum::MYSQL) {
            $sql = "ALTER TABLE {$table} COMMENT = '{$tableComment}';";
        }
        if ($driver == DbDriverEnum::PGSQL) {
            $sql = "COMMENT ON TABLE {$table} IS '{$tableComment}';";
        }
        if ($sql) {
            $this->runSqlQuery($schema, $sql);
        }
    }

}