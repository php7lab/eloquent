<?php

namespace PhpLab\Eloquent\Migration\Repositories\File;

use PhpLab\Eloquent\Migration\Entities\GenerateEntity;
use PhpLab\Eloquent\Migration\Interfaces\Repositories\GenerateRepositoryInterface;

class GenerateRepository implements GenerateRepositoryInterface
{

    protected $tableName = 'migration_generate';

    public function getEntityClass(): string
    {
        return GenerateEntity::class;
    }
}
