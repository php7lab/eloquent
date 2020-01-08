<?php

namespace PhpLab\Eloquent\Migration\Commands;

use php7extension\core\console\helpers\Output;
use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Sandbox\Console\Helpers\OutputHepler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownCommand extends BaseCommand
{
    protected static $defaultName = 'db:migrate:down';

    protected function configure()
    {
        parent::configure();
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Migration down')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command down all migrations...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<fg=white># Migrate DOWN</>');

        $historyCollection = $this->migrationService->allForDown();
        if (empty($historyCollection)) {
            $output->writeln(['', '<fg=magenta>- No applied migrations found! -</>', '']);
            return;
        }

        $withConfirm = $input->getOption('withConfirm');
        if ($withConfirm) {
            $versionArray = ArrayHelper::getColumn($historyCollection, 'version');
            $versionArray = array_values($versionArray);
            $output->writeln('');
            OutputHepler::writeList($output, $versionArray);
            $output->writeln('');
        }

        if (!$this->isContinueQuestion('Down migrations?', $input, $output)) {
            return;
        }

        $outputInfoCallback = function ($version) use ($output) {
            $output->writeln(' ' . $version);
        };
        $output->writeln('');
        $this->runMigrate($historyCollection, 'down', $outputInfoCallback);
        $output->writeln(['', '<fg=green>Migrate DOWN success!</>', '']);
    }

}
