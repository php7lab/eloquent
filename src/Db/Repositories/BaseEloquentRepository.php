<?php

namespace PhpLab\Eloquent\Db\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PhpLab\Sandbox\Common\Exceptions\NotFoundException;
use PhpLab\Domain\Repositories\BaseRepository;
use PhpLab\Eloquent\Db\Helpers\Manager;
use PhpLab\Eloquent\Db\Traits\TableNameTrait;

abstract class BaseEloquentRepository extends BaseRepository
{

    use TableNameTrait;

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

    protected function getQueryBuilder(): Builder
    {
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($this->tableName(), null, $this->connectionName());
        return $queryBuilder;
    }

    protected function getSchema(string $connectionName = null): \Illuminate\Database\Schema\Builder
    {
        $connection = $this->getConnection($connectionName);
        $schema = $connection->getSchemaBuilder();
        return $schema;
    }

    protected function allByBuilder(Builder $queryBuilder)
    {
        $postCollection = $queryBuilder->get();
        $array = $postCollection->toArray();
        return $this->forgeEntityCollection($array);
    }

    protected function oneByBuilder(Builder $queryBuilder)
    {
        $item = $queryBuilder->first();
        if (empty($item)) {
            throw new NotFoundException('Not found entity!');
        }
        return $this->forgeEntity($item);
    }

}
