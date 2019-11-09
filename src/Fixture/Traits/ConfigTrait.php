<?php

namespace PhpLab\Eloquent\Fixture\Traits;

trait ConfigTrait
{

    protected $config;

    public function loadConfig($mainConfigFile = null)
    {
        if($mainConfigFile == null) {
            $mainConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
        }
        $config = include(__DIR__ . '/../../../../../../' . $mainConfigFile);
        return $config;
    }

}