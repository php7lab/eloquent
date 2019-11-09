<?php

namespace PhpLab\Eloquent\Migration\Base;

use Illuminate\Database\Schema\Builder;
use PhpLab\Eloquent\Db\Traits\TableNameTrait;

abstract class BaseMigration
{

    use TableNameTrait;

    protected function runSqlQuery(Builder $schema, $sql)
    {
        $connection = $schema->getConnection();
        $rawSql = $connection->raw($sql);
        $connection->select($rawSql);
    }

}