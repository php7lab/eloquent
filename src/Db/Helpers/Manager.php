<?php

namespace PhpLab\Eloquent\Db\Helpers;

use Illuminate\Container\Container;
use PhpLab\Core\Helpers\DbHelper;
use PhpLab\Core\Legacy\Yii\Helpers\ArrayHelper;
use PhpLab\Core\Legacy\Yii\Helpers\FileHelper;
use PhpLab\Core\Libs\Env\DotEnvHelper;
use PhpLab\Eloquent\Db\Enums\DbDriverEnum;
use PhpLab\Eloquent\Db\Libs\TableAlias;
use PhpLab\Eloquent\Fixture\Traits\ConfigTrait;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

class Manager extends CapsuleManager
{

    use ConfigTrait;

    private $tableAlias;

    public function __construct(?Container $container = null, $mainConfigFile = null)
    {
        parent::__construct($container);
        $config = $this->loadConfig($mainConfigFile);
        $this->tableAlias = new TableAlias;
        $connections = DbHelper::getConfigFromEnv();
        foreach ($connections as $connectionName => $connectionConfig) {
            //dd($connectionConfig);
            if ( ! isset($connectionConfig['map'])) {
                $connectionConfig['map'] = ArrayHelper::getValue($config, 'connection.map', []);
            }
            $connectionConfig['database'] = $connectionConfig['dbname'];
            if($connectionConfig['driver'] == DbDriverEnum::SQLITE) {
                FileHelper::touch($connectionConfig['database']);
            }
            $this->addConnection($connectionConfig);
            $this->getAlias()->addMap($connectionName, ArrayHelper::getValue($connectionConfig, 'map', []));
        }
        $this->bootEloquent();
    }

    public function getAlias(): TableAlias
    {
        return $this->tableAlias;
    }

    private static function getConnections(array $config): array
    {
        $defaultConnection = ArrayHelper::getValue($config, 'defaultConnection');
        $connections = ArrayHelper::getValue($config, 'connections', []);
        if ($connections) {
            if (empty($defaultConnection)) {
                if ( ! empty($connections['default'])) {
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
            $connections = DbHelper::getConfigFromEnv();
            //dd($connections);
        }
        foreach ($connections as &$connection) {
            if(!empty($connection['dsn'])) {
                $connectionFromDsn = DbHelper::parseDsn($connection['dsn']);
                $connection = array_merge($connectionFromDsn, $connection);
            }
            if ($connection['driver'] == 'sqlite') {
                $connection['database'] = FileHelper::prepareRootPath($connection['database'] ?? null);
                unset($connection['host']);
            } else {
                //$connection['database'] = trim($connection['database'], '/');
                //$connection['host'] = $connection['host'] ?? '127.0.0.1';
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
