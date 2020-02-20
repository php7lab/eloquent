<?php

namespace PhpLab\Eloquent\Db\Base;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PhpLab\Core\Domain\Traits\ForgeEntityTrait;
use PhpLab\Eloquent\Db\Helpers\Manager;
use PhpLab\Eloquent\Db\Traits\TableNameTrait;
use PhpLab\Core\Exceptions\NotFoundException;

abstract class BaseEloquentRepository
{

    use TableNameTrait;
    use ForgeEntityTrait;

    protected $autoIncrement = 'id';
    private $capsule;

    public function __construct(Manager $capsule)
    {
        $this->capsule = $capsule;
    }

    public function autoIncrement()
    {
        return $this->autoIncrement;
    }

    public function getCapsule(): Manager
    {
        return $this->capsule;
    }

    public function getConnection(): Connection
    {
        $connection = $this->capsule->getConnection();
        return $connection;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($this->tableNameAlias(), null, $this->connectionName());
        return $queryBuilder;
    }

    protected function getSchema(string $connectionName = null): SchemaBuilder
    {
        $connection = $this->getConnection($connectionName);
        $schema = $connection->getSchemaBuilder();
        return $schema;
    }

    protected function allByBuilder(QueryBuilder $queryBuilder)
    {
        $postCollection = $queryBuilder->get();
        $array = $postCollection->toArray();
        return $this->forgeEntityCollection($array);
    }

    protected function oneByBuilder(QueryBuilder $queryBuilder)
    {
        $item = $queryBuilder->first();
        if (empty($item)) {
            throw new NotFoundException('Not found entity!');
        }
        return $this->forgeEntity($item);
    }

}
