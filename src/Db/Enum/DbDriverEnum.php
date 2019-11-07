<?php

namespace PhpLab\Eloquent\Db\Enum;

use php7rails\domain\BaseEnum;

class DbDriverEnum extends BaseEnum
{

    const MYSQL = 'mysql';
    const PGSQL = 'pgsql';
    const SQLITE = 'sqlite';
    const SQLSRV = 'sqlsrv';

}