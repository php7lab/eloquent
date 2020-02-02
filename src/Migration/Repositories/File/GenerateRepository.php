<?php

namespace PhpLab\Eloquent\Migration\Repositories\File;

use PhpLab\Core\Domain\Traits\ForgeEntityTrait;
use PhpLab\Eloquent\Migration\Interfaces\Repositories\GenerateRepositoryInterface;

class GenerateRepository implements GenerateRepositoryInterface
{

    use ForgeEntityTrait;

    protected $tableName = 'migration_generate';

    public function getEntityClass(): string
    {
        return 'PhpLab\\Eloquent\\Migration\\Entities\\GenerateEntity';
    }
}
