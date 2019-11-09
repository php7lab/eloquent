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
    //private static $capsule;

    /*public static function setConfig($name, $config) {
        ArrayHelper::setValue(self::$config, $name, $config);
    }

    public static function getConfig($name = null)
    {
        self::forgeConfig();
        return ArrayHelper::getValue(self::$config, $name);
    }*/

    /*public static function capsule() : Manager
    {
        self::createManager();
        return self::$capsule;
    }*/

    private static function forgeConfig()
    {
        if (!self::$config) {
            self::$config = include(__DIR__ . '/../../../../../../' . $_ENV['ELOQUENT_CONFIG_FILE']);
        }
    }

    /*public static function createManager()
    {
        if( ! isset(self::$capsule)) {
            $config = self::getConfig(self::CONNECTION);
            $connections = self::getConnections($config);
            self::$capsule = new Manager;
            self::$capsule->setAsGlobal();
            foreach ($connections as $connectionName => $config) {
                self::$capsule->addConnection($config);
                TableAliasHelper::addMap($connectionName, ArrayHelper::getValue($config, 'map', []));
            }
            self::$capsule->bootEloquent();
        }
    }*/

    /**
     * Страховка наличия файла БД SQLite (todo: костыль)
     *
     * Если файл БД не создан, то создает пустой файл
     *
     * @param string $connectionName
     * @return void
     */
    /*public static function forgeDb(string $connectionName = 'default'): void
    {
        $schema = Manager::schema($connectionName);
        $driver = $schema->getConnection()->getConfig('driver');
        if ($driver == DbDriverEnum::SQLITE) {
            $database = $schema->getConnection()->getConfig('database');
            FileHelper::touch($database);
        }
    }

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
    }*/

}
