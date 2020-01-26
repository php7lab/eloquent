<?php

namespace PhpLab\Eloquent\Migration\Scenarios\Input;

use Symfony\Component\Console\Question\Question;

class TableNameInputScenario extends BaseInputScenario
{

    protected function paramName()
    {
        return 'tableName';
    }

    public function isRequired(): bool
    {
        return true;
    }

    protected function question(): Question
    {
        $question = new Question('Enter table name: ');
        return $question;
    }

}
