<?php

namespace PhpLab\Eloquent\Migration\Commands;

use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Core\Console\Helpers\OutputHepler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends BaseCommand
{
    protected static $defaultName = 'db:migrate:up';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Migration up')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command up all migrations...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['<fg=white># Migrate UP</>']);

        $filteredCollection = $this->migrationService->allForUp();
        if (empty($filteredCollection)) {
            $output->writeln(['', '<fg=magenta>- Migrations up to date! -</>', '']);
            return;
        }

        $withConfirm = $input->getOption('withConfirm');
        if ($withConfirm) {
            $versionArray = ArrayHelper::getColumn($filteredCollection, 'version');
            $versionArray = array_values($versionArray);
            $output->writeln('');
            OutputHepler::writeList($output, $versionArray);
            $output->writeln('');
        }

        if ( ! $this->isContinueQuestion('Apply migrations?', $input, $output)) {
            return;
        }

        $outputInfoCallback = function ($version) use ($output) {
            $output->writeln(' ' . $version);
        };
        $output->writeln('');
        $this->runMigrate($filteredCollection, 'up', $outputInfoCallback);
        $output->writeln(['', '<fg=green>Migrate UP success!</>', '']);
    }

}
