<?php

namespace PhpLab\Eloquent\Db\Helpers;

use Illuminate\Container\Container;
use php7extension\yii\helpers\ArrayHelper;
use php7extension\yii\helpers\FileHelper;
use PhpLab\Eloquent\Fixture\Traits\ConfigTrait;

class Manager extends \Illuminate\Database\Capsule\Manager
{

    use ConfigTrait;

    private $tableAlias;

    public function __construct(?Container $container = null, $mainConfigFile = null)
    {
        parent::__construct($container);
        $config = $this->loadConfig($mainConfigFile);
        $this->config = $config['connection'];
        $connections = self::getConnections($this->config);

        $this->tableAlias = new TableAliasHelper;

        foreach ($connections as $connectionName => $config) {
            if (!isset($config['map'])) {
                $config['map'] = ArrayHelper::getValue($this->config, 'map', []);
            }
            $this->addConnection($config);
            $this->getAlias()->addMap($connectionName, ArrayHelper::getValue($config, 'map', []));
        }
        $this->bootEloquent();
    }

    public function getAlias(): TableAliasHelper
    {
        return $this->tableAlias;
    }

    private static function getConnections(array $config): array
    {
        $defaultConnection = ArrayHelper::getValue($config, 'defaultConnection');
        $connections = ArrayHelper::getValue($config, 'connections', []);
        if ($connections) {
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
            //$dsn = preg_replace('#^((?:pdo_)?sqlite?):///#', '$1://localhost/', $dsn);
            $dsnConfig = parse_url($dsn);
            $dsnConfig = array_map('rawurldecode', $dsnConfig);

            $connectionCofig = [
                'driver' => ArrayHelper::getValue($dsnConfig, 'scheme'),
                'host' => ArrayHelper::getValue($dsnConfig, 'host'),
                'database' => ArrayHelper::getValue($dsnConfig, 'path'),
                'username' => ArrayHelper::getValue($dsnConfig, 'user'),
                'password' => ArrayHelper::getValue($dsnConfig, 'pass'),
            ];
            if ($connectionCofig['driver'] == 'sqlite') {
                $connectionCofig['database'] = FileHelper::prepareRootPath($connectionCofig['host']);
                unset($connectionCofig['host']);
            }
            $connections = ['default' => $connectionCofig];
        }
        //dd($connections);
        foreach ($connections as &$connection) {

            if ($connection['driver'] == 'sqlite') {
                $connection['database'] = FileHelper::prepareRootPath($connection['database']);
                unset($connection['host']);
            } else {
                $connection['database'] = trim($connection['database'], '/');
            }
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
