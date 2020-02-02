<?php

namespace PhpLab\Eloquent\Migration\Repositories\File;

use PhpLab\Core\Domain\Base\BaseRepository;
use PhpLab\Eloquent\Migration\Interfaces\Repositories\GenerateRepositoryInterface;

class GenerateRepository extends BaseRepository implements GenerateRepositoryInterface
{

    protected $tableName = 'migration_generate';

    protected $entityClass = 'PhpLab\\Eloquent\\Migration\\Entities\\GenerateEntity';


}

