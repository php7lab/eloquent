<?php

namespace PhpLab\Eloquent\Fixture\Traits;

use php7extension\core\store\StoreFile;

trait ConfigTrait
{

    protected $config;

    public function loadConfig($mainConfigFile = null)
    {
        if ($mainConfigFile == null) {
            //$mainConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
        }
        $store = new StoreFile(__DIR__ . '/../../../../../../' . $mainConfigFile);
        $config = $store->load();
        return $config;
    }

}