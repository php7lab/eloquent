<?php

namespace PhpLab\Eloquent\Fixture\Commands;

use php7extension\yii\helpers\ArrayHelper;
use Illuminate\Support\Collection;
use PhpLab\Domain\Helpers\EntityHelper;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;
use PhpLab\Sandbox\Console\Question\ChoiceQuestion;
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
        $output->writeln('<fg=white># Fixture EXPORT</>');

        /** @var FixtureEntity[]|Collection $tableCollection */
        $tableCollection = $this->fixtureService->allTables();

        if($tableCollection->count() == 0) {
            $output->writeln('');
            $output->writeln('<fg=magenta>No tables in database!</>');
            $output->writeln('');
            return;
        }

        $withConfirm = $input->getOption('withConfirm');

        $tableNameArray = EntityHelper::getColumn($tableCollection, 'name');
        if ($withConfirm) {
            $output->writeln('');
            $question = new ChoiceQuestion(
                'Select tables for export',
                $tableNameArray,
                'a'
            );
            $question->setMultiselect(true);
            $selectedTables = $this->getHelper('question')->ask($input, $output, $question);
        } else {
            $selectedTables = $tableNameArray;
        }

        $output->writeln('');

        foreach ($selectedTables as $tableName) {
            $this->fixtureService->exportTable($tableName);
            $output->writeln(' ' . $tableName);
        }

        $output->writeln(['', '<fg=green>Fixture EXPORT success!</>', '']);
    }

}
