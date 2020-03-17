<?php

namespace PhpLab\Eloquent\Migration\Commands;

use PhpLab\Eloquent\Migration\Interfaces\Services\GenerateServiceInterface;
use PhpLab\Eloquent\Migration\Scenarios\Input\ActionInputScenario;
use PhpLab\Eloquent\Migration\Scenarios\Input\BaseInputScenario;
use PhpLab\Eloquent\Migration\Scenarios\Input\DomainNamespaceInputScenario;
use PhpLab\Eloquent\Migration\Scenarios\Input\TableNameInputScenario;
use PhpLab\Eloquent\Migration\Scenarios\Input\TypeInputScenario;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends BaseGeneratorCommand
{
    protected static $defaultName = 'db:migrate:generate';
    private $generateService;

    public function __construct(?string $name = null, GenerateServiceInterface $generateService)
    {
        $this->generateService = $generateService;
        parent::__construct($name);
    }

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Migration generate')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command generate migration...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=white># Migration generate </>');
        $dto = new \stdClass();
        $this->input($input, $output, $dto);
        $this->generateService->generate($dto);
        return 0;
    }

    private function input(InputInterface $input, OutputInterface $output, object $dto)
    {


        /*$dto->type = "create table";
        $dto->tableName = "qwerty";
        $dto->domainNamespace = "App\\Domain";
        return $dto;*/

        $this->runInputScenario(DomainNamespaceInputScenario::class, $input, $output, $dto);
        $this->runInputScenario(ActionInputScenario::class, $input, $output, $dto);
        $this->runInputScenario(TableNameInputScenario::class, $input, $output, $dto);
    }

    protected function runInputScenario(string $className, InputInterface $input, OutputInterface $output, $dto)
    {
        $output->writeln('');
        /** @var BaseInputScenario $inputScenario */
        $inputScenario = new $className;
        $inputScenario->helper = $this->getHelper('question');
        $inputScenario->input = $input;
        $inputScenario->output = $output;
        $inputScenario->dto = $dto;
        return $inputScenario->run();
    }

}
