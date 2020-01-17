<?php

namespace PhpLab\Eloquent\Migration\Enums;

use PhpLab\Domain\Data\BaseEnum;

class GenerateActionEnum extends BaseEnum
{

    const CREATE_TABLE = 'create table';
    const ADD_COLUMN = 'add column';

}