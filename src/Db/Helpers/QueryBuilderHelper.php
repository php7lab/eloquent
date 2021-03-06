<?php

namespace PhpLab\Eloquent\Db\Helpers;

use Illuminate\Database\Query\Builder;
use PhpLab\Core\Domain\Libs\Query;
use PhpLab\Core\Domain\Entities\Query\Where;

class QueryBuilderHelper
{

    public static function setWhere(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::WHERE])) {
            foreach ($queryArr[Query::WHERE] as $key => $value) {
                if (is_array($value)) {
                    $queryBuilder->whereIn($key, $value);
                } else {
                    $queryBuilder->where($key, $value);
                }
            }
        }

        $whereArray = $query->getWhereNew();
        if ( ! empty($whereArray)) {
            /** @var Where $where */
            foreach ($whereArray as $where) {
                if (is_array($where->value)) {
                    $queryBuilder->whereIn($where->column, $where->value, $where->boolean, $where->not);
                } else {
                    $queryBuilder->where($where->column, $where->operator, $where->value, $where->boolean);
                }
            }
        }
    }

    public static function setOrder(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::ORDER])) {
            foreach ($queryArr[Query::ORDER] as $field => $direction) {
                $queryBuilder->orderBy($field, self::encodeDirection($direction));
            }
        }
    }

    private static function encodeDirection($direction)
    {
        $directions = [
            SORT_ASC => 'asc',
            SORT_DESC => 'desc',
        ];
        return $directions[$direction];
    }

    public static function setSelect(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::SELECT])) {
            $queryBuilder->select($queryArr[Query::SELECT]);
        }
    }

    public static function setPaginate(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::LIMIT])) {
            $queryBuilder->limit($queryArr[Query::LIMIT]);
        }
        if ( ! empty($queryArr[Query::OFFSET])) {
            $queryBuilder->offset($queryArr[Query::OFFSET]);
        }
    }

}