<?php

namespace PhpLab\Eloquent\Db\Repositories;

use Doctrine\Common\Util\Inflector;
use php7extension\core\exceptions\NotFoundException;
use php7extension\core\helpers\ClassHelper;
use php7extension\yii\helpers\ArrayHelper;
use php7rails\domain\BaseEntityWithId;
use php7rails\domain\data\Query;
use PhpLab\Domain\Helpers\EntityHelper;
use PhpLab\Domain\Interfaces\CrudRepositoryInterface;
use PhpLab\Eloquent\Db\Helpers\QueryBuilderHelper;
use PhpLab\Eloquent\Db\Helpers\QueryFilter;

abstract class BaseEloquentCrudRepository extends BaseEloquentRepository implements CrudRepositoryInterface
{

    protected $primaryKey = ['id'];

    public function relations()
    {
        return [];
    }

    public function primaryKey()
    {
        return $this->primaryKey;
    }

    protected function queryFilterInstance(Query $query = null)
    {
        $query = Query::forge($query);
        /** @var QueryFilter $queryFilter */
        $queryFilter = new QueryFilter($this, $query);
        return $queryFilter;
    }

    public function count(Query $query = null): int
    {
        $query = Query::forge($query);
        $queryBuilder = $this->getQueryBuilder();
        QueryBuilderHelper::setWhere($query, $queryBuilder);
        return $queryBuilder->count();
    }

    public function _all(Query $query = null)
    {
        $query = Query::forge($query);
        $queryBuilder = $this->getQueryBuilder();
        QueryBuilderHelper::setWhere($query, $queryBuilder);
        QueryBuilderHelper::setSelect($query, $queryBuilder);
        QueryBuilderHelper::setOrder($query, $queryBuilder);
        QueryBuilderHelper::setPaginate($query, $queryBuilder);
        $collection = $this->allByBuilder($queryBuilder);
        return $collection;
    }

    public function all(Query $query = null)
    {
        $query = Query::forge($query);
        $queryFilter = $this->queryFilterInstance($query);
        $queryWithoutRelations = $queryFilter->getQueryWithoutRelations();
        $collection = $this->_all($queryWithoutRelations);
        $collection = $queryFilter->loadRelations($collection);
        return $collection;

    }

    public function oneById($id, Query $query = null)
    {
        $query = Query::forge($query);
        $query->where('id', $id);
        return $this->one($query);
    }

    public function one(Query $query = null)
    {
        $query->limit(1);
        $collection = $this->all($query);
        if ($collection->count() < 1) {
            throw new NotFoundException('Not found entity!');
        }
        return $collection->first();
    }

    /**
     * @param BaseEntityWithId $entity
     */
    public function create($entity)
    {
        $columnList = $this->getColumnsForModify();
        $arraySnakeCase = EntityHelper::toArrayForTablize($entity, $columnList);
        $queryBuilder = $this->getQueryBuilder();
        $lastId = $queryBuilder->insertGetId($arraySnakeCase);
        $entity->setId($lastId);
    }

    private function getColumnsForModify()
    {
        $schema = $this->getSchema();
        $columnList = $schema->getColumnListing($this->tableName());
        if ($this->autoIncrement()) {
            ArrayHelper::removeByValue($this->autoIncrement(), $columnList);
        }
        return $columnList;
    }

    public function updateById($id, $data)
    {
        $columnList = $this->getColumnsForModify();
        $data = ArrayHelper::extractByKeys($data, $columnList);
        $entity = $this->oneById($id);
        EntityHelper::setAttributes($entity, $data);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->find($id);
        $queryBuilder->update($data);
    }

    public function deleteById($id)
    {
        $this->oneById($id);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->delete($id);
    }

}