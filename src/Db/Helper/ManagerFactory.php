<?php

namespace PhpLab\Eloquent\Db\Helper;

use PhpLab\Eloquent\Db\Enum\DbDriverEnum;
use Illuminate\Database\Capsule\Manager;
use php7extension\yii\helpers\ArrayHelper;
use php7extension\yii\helpers\FileHelper;

class ManagerFactory
{

    const CONNECTION = 'connection';
    const FIXTURE = 'fixture';
    const MIGRATE = 'migrate';

    private static $config;

    public static function setConfig($name, $config) {
        ArrayHelper::setValue(self::$config, $name, $config);
    }

    public static function getConfig($name = null) {
        return ArrayHelper::getValue(self::$config, $name);
    }

    public static function createManager(array $mainConfig = null) : Manager {
        if($mainConfig && empty(self::$config)) {
            self::$config = $mainConfig;
        }
        $config = self::getConfig(self::CONNECTION);
        $connections = self::getConnections($config);
        $capsule = new Manager;
        $capsule->setAsGlobal();
        foreach ($connections as $connectionName => $config) {
            $capsule->addConnection($config);
            TableAliasHelper::addMap($connectionName, ArrayHelper::getValue($config, 'map', []));
        }
        $capsule->bootEloquent();
        return $capsule;
    }

    /**
     * Страховка наличия файла БД SQLite (todo: костыль)
     *
     * Если файл БД не создан, то создает пустой файл
     *
     * @param string $connectionName
     * @return void
     */
    public static function forgeDb(string $connectionName = 'default') : void {
        $schema = Manager::schema($connectionName);
        $driver = $schema->getConnection()->getConfig('driver');
        if($driver == DbDriverEnum::SQLITE) {
            $database = $schema->getConnection()->getConfig('database');
            FileHelper::touch($database);
        }
    }

    private static function getConnections(array $config) : array {
        $defaultConnection = ArrayHelper::getValue($config, 'defaultConnection');
        $connections = ArrayHelper::getValue($config, 'connections', []);

        if(empty($defaultConnection)) {
            if( ! empty($connections['default'])) {
                $defaultConnection = 'default';
            } else {
                $defaultConnection = ArrayHelper::firstKey($connections);
            }
        }

        if($defaultConnection != 'default') {
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
