<?php

namespace PhpLab\Eloquent\Db\Helper;

use Illuminate\Container\Container;
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
        $connections = self::getConnections($this->config);
        foreach ($connections as $connectionName => $config) {
            if( ! isset($config['map'])) {
                $config['map'] = ArrayHelper::getValue($this->config, 'map', []);
            }
            $this->addConnection($config);
            TableAliasHelper::addMap($connectionName, ArrayHelper::getValue($config, 'map', []));
        }
        $this->bootEloquent();
    }

    private static function getConnections(array $config): array
    {
        $defaultConnection = ArrayHelper::getValue($config, 'defaultConnection');
        $connections = ArrayHelper::getValue($config, 'connections', []);
        if($connections) {
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
        } else {
            $dsn = $_ENV['DATABASE_URL'];
            $dsn = preg_replace('#^((?:pdo_)?sqlite?):///#', '$1://localhost/', $dsn);
            $dsnConfig = parse_url($dsn);
            $dsnConfig = array_map('rawurldecode', $dsnConfig);
            $connectionCofig = [
                'driver' => ArrayHelper::getValue($dsnConfig, 'scheme'),
                'host' => ArrayHelper::getValue($dsnConfig, 'host'),
                'database' => trim(ArrayHelper::getValue($dsnConfig, 'path'), '/'),
                'username' => ArrayHelper::getValue($dsnConfig, 'user'),
                'password' => ArrayHelper::getValue($dsnConfig, 'pass'),
            ];
            $connections = ['default' => $connectionCofig];
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
