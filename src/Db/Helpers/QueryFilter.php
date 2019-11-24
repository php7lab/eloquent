<?php

namespace PhpLab\Eloquent\Db\Helpers;

use php7rails\domain\data\Query;
use php7rails\domain\repositories\BaseRepository;
use PhpLab\Domain\Data\Collection;
use PhpLab\Domain\Helpers\Repository\RelationHelper;
use PhpLab\Domain\Helpers\Repository\RelationWithHelper;
use PhpLab\Domain\Interfaces\ReadAllServiceInterface;
use PhpLab\Domain\Interfaces\RelationConfigInterface;

/**
 * Class QueryFilter
 *
 * @package PhpLab\Domain\Helpers\Repository
 *
 */
class QueryFilter
{

    /**
     * @var BaseRepository|RelationConfigInterface
     */
    private $repository;
    private $query;
    private $with;

    public function __construct(ReadAllServiceInterface $repository, Query $query)
    {
        $this->repository = $repository;
        $this->query = $query;
    }

    public function getQueryWithoutRelations(): Query
    {
        $query = clone $this->query;
        $this->with = RelationWithHelper::cleanWith($this->repository->relations(), $query);
        return $query;
    }

    public function loadRelations(Collection $data)
    {
        if (empty($this->with)) {
            return $data;
        }
        $collection = RelationHelper::load($this->repository, $this->query, $data);
        //dd($collection);
        return $collection;
    }

    /*public function getQuery() : Query {
        if(!isset($this->query)) {
            $this->query = Query::forge();
        }
        return $this->query;
    }
    
    public function setQuery(Query $query) {
        $this->query = clone $query;
    }*/

}
