<?php

namespace PhpLab\Eloquent\Db\Repository;

use PhpLab\Domain\Repository\BaseRepository;
use PhpLab\Eloquent\Db\Traits\TableNameTrait;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;
use php7extension\core\exceptions\NotFoundException;

abstract class BaseDbRepository extends BaseRepository
{

    use TableNameTrait;

    protected $autoIncrement = 'id';

    public function autoIncrement()
    {
        return $this->autoIncrement;
    }

    protected function getQueryBuilder() : Builder
    {
        $queryBuilder = Manager::table($this->tableName(), null, $this->connectionName());
        return $queryBuilder;
    }

    protected function getSchema() : \Illuminate\Database\Schema\Builder
    {
        $schema = Manager::schema($this->connectionName());
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
