<?php

namespace PhpLab\Eloquent\Fixture\Commands;

use php7extension\core\console\helpers\input\Select;
use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Domain\Data\Collection;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends BaseCommand
{
    protected static $defaultName = 'db:fixture:export';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Export fixture data to files')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['<fg=white># Fixture EXPORT</>']);

        /** @var FixtureEntity[]|Collection $tableCollection */
        $tableCollection = $this->fixtureService->allTables();

        $withConfirm = $input->getOption('withConfirm');
        $tableArray = ArrayHelper::getColumn($tableCollection->toArray(), 'name');
        if ($withConfirm) {
            $selectedTables = Select::display('Select tables for export', $tableArray, 1);
            $selectedTables = array_values($selectedTables);
        } else {
            $selectedTables = $tableArray;
        }

        $output->writeln('');

        foreach ($selectedTables as $tableName) {
            $this->fixtureService->exportTable($tableName);
            $output->writeln(' * ' . $tableName);
        }

        $output->writeln(['', '<fg=green>Fixture EXPORT success!</>', '']);
    }

}
