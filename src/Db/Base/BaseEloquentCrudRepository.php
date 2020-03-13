<?php

namespace PhpLab\Eloquent\Db\Base;

use Illuminate\Database\QueryException;
use PhpLab\Core\Domain\Enums\OperatorEnum;
use PhpLab\Core\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Domain\Interfaces\Entity\EntityIdInterface;
use PhpLab\Core\Domain\Interfaces\Repository\CrudRepositoryInterface;
use PhpLab\Core\Domain\Libs\Query;
use PhpLab\Core\Exceptions\NotFoundException;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
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

    protected function forgeQuery(Query $query = null)
    {
        $query = Query::forge($query);
        return $query;
    }

    protected function queryFilterInstance(Query $query = null)
    {
        $query = $this->forgeQuery($query);
        /** @var QueryFilter $queryFilter */
        $queryFilter = new QueryFilter($this, $query);
        return $queryFilter;
    }

    public function count(Query $query = null): int
    {
        $query = $this->forgeQuery($query);
        $queryBuilder = $this->getQueryBuilder();
        QueryBuilderHelper::setWhere($query, $queryBuilder);
        return $queryBuilder->count();
    }

    public function _all(Query $query = null)
    {
        $query = $this->forgeQuery($query);
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
        $query = $this->forgeQuery($query);
        $queryFilter = $this->queryFilterInstance($query);
        $queryWithoutRelations = $queryFilter->getQueryWithoutRelations();
        $collection = $this->_all($queryWithoutRelations);
        $collection = $queryFilter->loadRelations($collection);
        return $collection;
    }

    public function oneById($id, Query $query = null): EntityIdInterface
    {
        $query = $this->forgeQuery($query);
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

    public function create(EntityIdInterface $entity)
    {
        $columnList = $this->getColumnsForModify();
        $arraySnakeCase = EntityHelper::toArrayForTablize($entity, $columnList);
        $queryBuilder = $this->getQueryBuilder();
        try {
            $lastId = $queryBuilder->insertGetId($arraySnakeCase);
            $entity->setId($lastId);
        } catch (QueryException $e) {
            $errors = new UnprocessibleEntityException;
            if($_ENV['APP_DEBUG']) {
                $message = $e->getMessage();
                $message = preg_replace('/(\s+)/i', ' ', $message);
                $message = str_replace("'", "\\'", $message);
                $message = trim($message);
            } else {
                $message = 'Database error!';
            }
            $errors->add('', $message);
            throw $errors;
        }
    }

    private function getColumnsForModify()
    {
        $schema = $this->getSchema();
        $columnList = $schema->getColumnListing($this->tableNameAlias());
        if ($this->autoIncrement()) {
            ArrayHelper::removeByValue($this->autoIncrement(), $columnList);
        }
        return $columnList;
    }

    /*public function persist(EntityIdInterface $entity)
    {

    }*/

    public function update(EntityIdInterface $entity)
    {
        $this->oneById($entity->getId());
        $data = EntityHelper::toArrayForTablize($entity);
        $this->updateQuery($entity->getId(), $data);
        //$this->updateById($entity->getId(), $data);
    }

    /*public function updateById($id, $data)
    {
        $this->oneById($id);
        $this->updateQuery($id, $data);
    }*/

    private function updateQuery($id, array $data)
    {
        $columnList = $this->getColumnsForModify();
        $data = ArrayHelper::extractByKeys($data, $columnList);
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

    public function deleteByCondition(array $condition)
    {
        $queryBuilder = $this->getQueryBuilder();
        foreach ($condition as $key => $value) {
            $queryBuilder->where($key, OperatorEnum::EQUAL, $value);
        }
        $queryBuilder->delete();
    }

}