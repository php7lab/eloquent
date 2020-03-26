<?php

namespace PhpLab\Eloquent\Fixture\Commands;

use Illuminate\Database\Eloquent\Collection;
use PhpLab\Core\Console\Widgets\LogWidget;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Eloquent\Fixture\Entities\FixtureEntity;
use PhpLab\Core\Console\Question\ChoiceQuestion;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=white># Fixture IMPORT</>');

        /** @var FixtureEntity[]|Collection $tableCollection */
        $tableCollection = $this->fixtureService->allFixtures();

        $withConfirm = $input->getOption('withConfirm');
        $tableArray = ArrayHelper::getColumn($tableCollection->toArray(), 'name');
        if ($withConfirm) {
            $output->writeln('');
            $question = new ChoiceQuestion(
                'Select tables for import',
                $tableArray,
                'a'
            );
            $question->setMultiselect(true);
            $selectedTables = $this->getHelper('question')->ask($input, $output, $question);
        } else {
            $selectedTables = $tableArray;
        }

        $output->writeln('');

        $logWidget = new LogWidget($output);

        foreach ($selectedTables as $tableName) {
            $logWidget->start(' ' . $tableName);
            $this->fixtureService->importTable($tableName);
            $logWidget->finishSuccess();
        }

        $output->writeln(['', '<fg=green>Fixture IMPORT success!</>', '']);
        return 0;
    }

}
