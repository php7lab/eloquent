<?php

namespace PhpLab\Eloquent\Fixture\Command;

use Illuminate\Database\Eloquent\Collection;
use php7extension\core\console\helpers\input\Select;
use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Eloquent\Fixture\Entity\FixtureEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends BaseCommand
{
    protected static $defaultName = 'db:fixture:import';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Import fixture data to DB')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['<fg=white># Fixture IMPORT</>']);

        /** @var FixtureEntity[]|Collection $tableCollection */
        $tableCollection = $this->fixtureService->allFixtures();

        $withConfirm = $input->getOption('withConfirm');
        $tableArray = ArrayHelper::getColumn($tableCollection->toArray(), 'name');
        if ($withConfirm) {
            $selectedTables = Select::display('Select tables for import', $tableArray, 1);
            $selectedTables = array_values($selectedTables);
        } else {
            $selectedTables = $tableArray;
        }

        $output->writeln('');

        foreach ($selectedTables as $tableName) {
            $this->fixtureService->importTable($tableName);
            $output->writeln(' * ' . $tableName);
        }

        $output->writeln(['', '<fg=green>Fixture IMPORT success!</>', '']);
    }

}
