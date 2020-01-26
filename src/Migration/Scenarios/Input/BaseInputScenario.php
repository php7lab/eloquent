<?php

namespace PhpLab\Eloquent\Migration\Scenarios\Input;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class BaseInputScenario
{

    /** @var QuestionHelper */
    public $helper;

    /** @var InputInterface */
    public $input;

    /** @var OutputInterface */
    public $output;

    /** @var object */
    public $dto;

    abstract protected function paramName();

    abstract protected function question(): Question;

    public function isRequired(): bool
    {
        return false;
    }

    public function run()
    {
        $question = $this->question();
        $paramName = $this->paramName();
        do {
            $value = $this->helper->ask($this->input, $this->output, $question);
        } while ($this->isRequired() && empty($value));
        $this->dto->{$paramName} = $value;
    }

}
