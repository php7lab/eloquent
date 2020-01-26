<?php

namespace PhpLab\Eloquent\Db\Enums;

use PhpLab\Domain\Base\BaseEnum;

class DbDriverEnum extends BaseEnum
{

    const MYSQL = 'mysql';
    const PGSQL = 'pgsql';
    const SQLITE = 'sqlite';
    const SQLSRV = 'sqlsrv';

}