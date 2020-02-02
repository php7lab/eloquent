<?php

namespace PhpLab\Eloquent\Migration\Services;

use PhpLab\Core\Domain\Base\BaseService;
use PhpLab\Eloquent\Migration\Interfaces\Repositories\GenerateRepositoryInterface;
use PhpLab\Eloquent\Migration\Interfaces\Services\GenerateServiceInterface;
use PhpLab\Eloquent\Migration\Scenarios\Render\CreateTableRender;
use PhpLab\Core\Helpers\ClassHelper;

class GenerateService extends BaseService implements GenerateServiceInterface
{

    public function __construct(GenerateRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function generate(object $dto)
    {


        //if($dto->type == GenerateActionEnum::CREATE_TABLE) {
        $class = CreateTableRender::class;
        //}

        //dd($dto);
        $dto->attributes = [];

        $dto->attributes = [];

        $scenarioInstance = new $class;
        $scenarioParams = [
            'dto' => $dto,
        ];
        ClassHelper::configure($scenarioInstance, $scenarioParams);
        //$scenarioInstance->init();
        $scenarioInstance->run();

        //dd($dto);
    }

}

