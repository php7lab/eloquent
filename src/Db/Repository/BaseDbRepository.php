<?php

namespace PhpLab\Eloquent\Db\Repository;

use Illuminate\Database\Connection;
use PhpLab\Domain\Repository\BaseRepository;
use PhpLab\Eloquent\Db\Helper\ManagerFactory;
use PhpLab\Eloquent\Db\Traits\TableNameTrait;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;
use php7extension\core\exceptions\NotFoundException;

abstract class BaseDbRepository extends BaseRepository
{

    use TableNameTrait;

    protected $autoIncrement = 'id';
    private $capsule;

    public function __construct(\PhpLab\Eloquent\Db\Helper\Manager $capsule)
    {
        $this->capsule = $capsule;
    }

    public function autoIncrement()
    {
        return $this->autoIncrement;
    }

    public function getConnection() : Connection
    {
        $connection = $this->capsule->getConnection();
        return $connection;
    }

    protected function getQueryBuilder() : Builder
    {
        //$capsule = ManagerFactory::capsule();
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($this->tableName(), null, $this->connectionName());
        //dd($queryBuilder);
        //$queryBuilder = $this->capsule->getConnection($this->connectionName())->table($this->tableName(), null);
        //$queryBuilder = $capsule->table($this->tableName(), null, $this->connectionName());
        return $queryBuilder;
    }

    protected function getSchema(string $connectionName = null) : \Illuminate\Database\Schema\Builder
    {
        $connection = $this->getConnection($connectionName);
        $schema = $connection->getSchemaBuilder();


        //$capsule = ManagerFactory::capsule();
        //$schema = $this->capsule->schema($this->connectionName());

        //$schema = $capsule::schema($this->connectionName());
        return $schema;
    }

    protected function allByBuilder(Builder $queryBuilder) {
        $postCollection = $queryBuilder->get();
        $array = $postCollection->toArray();
        return $this->forgeEntityCollection($array);
    }

    protected function oneByBuilder(Builder $queryBuilder) {
        $item = $queryBuilder->first();
        if(empty($item)) {
            throw new NotFoundException('Not found entity!');
        }
        return $this->forgeEntity($item);
    }

}
