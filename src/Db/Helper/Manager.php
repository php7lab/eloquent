<?php

namespace PhpLab\Eloquent\Db\Helper;

use Illuminate\Container\Container;
//use Illuminate\Database\Capsule\Manager as IlluminateManager;
use php7extension\yii\helpers\ArrayHelper;
use PhpLab\Eloquent\Fixture\Traits\ConfigTrait;

class Manager extends \Illuminate\Database\Capsule\Manager
{

    use ConfigTrait;

    public function __construct(?Container $container = null, $mainConfigFile = null)
    {
        parent::__construct($container);

        $config = $this->loadConfig($mainConfigFile);
        $this->config = $config['connection'];

        //self::forgeConfig();
        //$config = $this->getConfig(ManagerFactory::CONNECTION);
        $connections = self::getConnections($this->config);
        //dd($connections);
        //self::$capsule = new Manager;
        //self::$capsule->setAsGlobal();
        foreach ($connections as $connectionName => $config) {
            $this->addConnection($config);
            TableAliasHelper::addMap($connectionName, ArrayHelper::getValue($config, 'map', []));
        }
        $this->bootEloquent();
    }

    /*public function getConfig($name = null)
    {
        return ArrayHelper::getValue($this->config, $name);
    }

    private static function forgeConfig()
    {
        $mainConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
        if (!$this->config) {
            $this->config = include(__DIR__ . '/../../../../../../' . $mainConfigFile);
        }
    }*/

    private static function getConnections(array $config): array
    {
        $defaultConnection = ArrayHelper::getValue($config, 'defaultConnection');
        $connections = ArrayHelper::getValue($config, 'connections', []);

        if (empty($defaultConnection)) {
            if (!empty($connections['default'])) {
                $defaultConnection = 'default';
            } else {
                $defaultConnection = ArrayHelper::firstKey($connections);
            }
        }

        if ($defaultConnection != 'default') {
            $connections['default'] = $connections[$defaultConnection];
            unset($connections[$defaultConnection]);
        }
        return $connections;
    }

}



/* Пример конфига
return [
    'defaultConnection' => 'mysqlServer',
    //'defaultConnection' => 'sqliteServer',
    //'defaultConnection' => 'pgsqlServer',
    'connections' => [
        'mysqlServer' => [
            "driver" => 'mysql',
            "host" => 'localhost',
            "database" => 'symfony4',
            "username" => 'root',
            "password" => '',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
        'sqliteServer' => [
            "driver" => 'sqlite',
            "database" => __DIR__ . '/../../var/sqlite/default.sqlite',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
        'pgsqlServer' => [
            "driver" => DbDriverEnum::PGSQL,
            "host" => 'localhost',
            "database" => 'symfony4',
            "username" => 'postgres',
            "password" => 'postgres',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
    ],
];
 */
