<?php

namespace PhpLab\Eloquent\Db\Traits;

trait TableNameTrait
{

    protected $connectionName = 'default';
    protected $tableName;

    //abstract function getCapsule() : Manager;

    public function connectionName()
    {
        return $this->connectionName;
    }

    public function tableName()
    {
        return $this->encodeTableName($this->tableName);
    }

    public function encodeTableName(string $sourceTableName): string
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode($this->connectionName(), $sourceTableName);
        return $targetTableName;
    }

}