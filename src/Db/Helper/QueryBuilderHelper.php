<?php

namespace PhpLab\Eloquent\Db\Helper;

use Illuminate\Database\Query\Builder;
use php7rails\domain\data\Query;

class QueryBuilderHelper
{

    public static function setWhere(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if (!empty($queryArr[Query::WHERE])) {
            foreach ($queryArr[Query::WHERE] as $key => $value) {
                if (is_array($value)) {
                    $queryBuilder->whereIn($key, $value);
                } else {
                    $queryBuilder->where($key, $value);
                }
            }
        }
    }

    public static function setOrder(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if (!empty($queryArr[Query::ORDER])) {
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
        if (!empty($queryArr[Query::SELECT])) {
            $queryBuilder->select($queryArr[Query::SELECT]);
        }
    }

    public static function setPaginate(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if (!empty($queryArr[Query::LIMIT])) {
            $queryBuilder->limit($queryArr[Query::LIMIT]);
        }
        if (!empty($queryArr[Query::OFFSET])) {
            $queryBuilder->offset($queryArr[Query::OFFSET]);
        }
    }

}