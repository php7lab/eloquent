<?php

namespace PhpLab\Eloquent\Db\Base;

use Doctrine\DBAL\Driver\PDOStatement;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\QueryException;
use PhpLab\Core\Domain\Enums\OperatorEnum;
use PhpLab\Core\Domain\Exceptions\UnprocessibleEntityException;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Domain\Interfaces\Entity\EntityIdInterface;
use PhpLab\Core\Domain\Interfaces\Repository\CrudRepositoryInterface;
use PhpLab\Core\Domain\Libs\Query;
use PhpLab\Core\Exceptions\NotFoundException;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Eloquent\Db\Helpers\DoctrineQueryBuilderHelper;
use PhpLab\Eloquent\Db\Helpers\QueryFilter;

abstract class BaseDoctrineCrudRepository extends BaseDoctrineRepository implements CrudRepositoryInterface
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
        DoctrineQueryBuilderHelper::setWhere($query, $queryBuilder);
        return $this->countByBuilder($queryBuilder);
    }

    public function _all(Query $query = null)
    {
        $query = $this->forgeQuery($query);
        $queryBuilder = $this->getQueryBuilder();
        DoctrineQueryBuilderHelper::setWhere($query, $queryBuilder);
        DoctrineQueryBuilderHelper::setSelect($query, $queryBuilder);
        DoctrineQueryBuilderHelper::setOrder($query, $queryBuilder);
        DoctrineQueryBuilderHelper::setPaginate($query, $queryBuilder);
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

    private function getLastId($query) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $lastId = $stmt->fetch()['id'];
        return $lastId;
    }

    public function create(EntityIdInterface $entity)
    {

        $columnList = $this->getColumnsForModify();
        $arraySnakeCase = EntityHelper::toArrayForTablize($entity, $columnList);

        $queryBuilder = $this->getQueryBuilder();

        foreach ($arraySnakeCase as $key => &$item) {
            if($item instanceof \DateTime) {
                $item = $item->format('Y-m-d H:i:s');
            }
            //$item = $queryBuilder->createNamedParameter($item);
        }

        try {
            //print_r($arraySnakeCase);exit;
            $queryBuilder = $queryBuilder
                ->insert($this->tableNameAlias())
                ->values($arraySnakeCase);

            $lastId = $this->executeQuery($queryBuilder);
            print_r($lastId);exit;
            //print_r($lastId);exit;
            $entity->setId($lastId);
        } catch (QueryException $e) {
            $errors = new UnprocessibleEntityException;
            $errors->add('', 'Already exists!');
            throw $errors;
        }
    }

    private function getColumnsForModify()
    {
        $schema = $this->getSchema();
        $columnList = $schema->listTableColumns($this->tableNameAlias());
        $columnList = array_keys($columnList);
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
        $entity = $this->oneById($id);
        $queryBuilder = $this->getQueryBuilder();
        $predicates = $queryBuilder->expr()->andX();
        $predicates->add($queryBuilder->expr()->eq('id', $entity->getId()));
        $this->deleteByPredicates($predicates, $queryBuilder);
    }

    public function deleteByCondition(array $condition)
    {
        $queryBuilder = $this->getQueryBuilder();
        $predicates = $queryBuilder->expr()->andX();
        foreach ($condition as $key => $value) {
            $predicates->add($queryBuilder->expr()->eq($key, $value));
        }
        $this->deleteByPredicates($predicates, $queryBuilder);
    }

    private function deleteByPredicates($predicates, QueryBuilder $queryBuilder): PDOStatement {
        $queryBuilder = $queryBuilder
            ->delete($this->tableNameAlias())
            ->where($predicates);
        return $this->executeQuery($queryBuilder);
    }

}