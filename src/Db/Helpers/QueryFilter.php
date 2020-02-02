<?php

namespace PhpLab\Eloquent\Db\Helpers;

use Illuminate\Support\Collection;
use php7rails\domain\repositories\BaseRepository;
use PhpLab\Core\Domain\Libs\Query;
use PhpLab\Core\Domain\Helpers\Repository\RelationHelper;
use PhpLab\Core\Domain\Helpers\Repository\RelationWithHelper;
use PhpLab\Core\Domain\Interfaces\ReadAllInterface;
use PhpLab\Core\Domain\Interfaces\Repository\RelationConfigInterface;

/**
 * Class QueryFilter
 *
 * @package PhpLab\Core\Domain\Helpers\Repository
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

    public function __construct(ReadAllInterface $repository, Query $query)
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
