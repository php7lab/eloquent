<?php

namespace PhpLab\Eloquent\Migration\Scenarios\Input;

use PhpLab\Eloquent\Migration\Enums\GenerateActionEnum;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ActionInputScenario extends BaseInputScenario
{

    public function choices()
    {
        return GenerateActionEnum::values();
    }

    protected function paramName()
    {
        return 'type';
    }

    protected function question(): Question
    {
        $question = new ChoiceQuestion(
            'Please select type',
            $this->choices()
        );
        return $question;
    }

}
