<?php

namespace PhpLab\Eloquent\Fixture\Traits;

use PhpLab\Core\Libs\Store\StoreFile;

trait ConfigTrait
{

    protected $config;

    public function loadConfig($mainConfigFile = null)
    {
        if ($mainConfigFile == null) {
            $mainConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
        }
        $store = new StoreFile(__DIR__ . '/../../../../../../' . $mainConfigFile);
        $config = $store->load();
        return $config;
    }

}