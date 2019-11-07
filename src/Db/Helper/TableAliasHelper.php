<?php

namespace PhpLab\Eloquent\Db\Helper;

use php7extension\yii\helpers\ArrayHelper;

class TableAliasHelper
{

    private static $map = null;
    private static $connectionMaps = [];

    public static function addMap(string $connectionName, array $map) {
        self::$connectionMaps[$connectionName] = $map;
    }

    public static function encode(string $connectionName, string $sourceTableName) {
        $map = self::$connectionMaps[$connectionName];
        $targetTableName = ArrayHelper::getValue($map, $sourceTableName, $sourceTableName);
        return $targetTableName;
    }

    public static function decode(string $connectionName, string $targetTableName) {
        $map = self::$connectionMaps[$connectionName];
        $map = array_flip($map);
        $sourceTableName = ArrayHelper::getValue($map, $targetTableName, $targetTableName);
        return $sourceTableName;
    }




    public static function getLocalName(string $tableName, array $map = null) {
        if(isset($map)) {
            $map = array_flip($map);
            $globalName = ArrayHelper::getValue($map, $tableName);
        } else {
            self::loadMap();
            $map = array_flip(self::$map);
            $globalName = ArrayHelper::getValue($map, $tableName);
        }
        if($globalName) {
            $tableName = $globalName;
        }
        return $tableName;
    }

    public static function getGlobalName(string $tableName, array $map = null) {
        if(isset($map)) {
            $globalName = ArrayHelper::getValue($map, $tableName);
        } else {
            self::loadMap();
            $globalName = ArrayHelper::getValue(self::$map, $tableName);
        }
        if($globalName) {
            $tableName = $globalName;
        }
        return $tableName;
    }

    private static function loadMap() {
        if(self::$map === null) {
            $config = EnvService::getConnection('main');
            if($config['driver'] == 'pgsql') {
                self::$map = ArrayHelper::getValue($config, 'map', []);
            } else {
                self::$map = [];
            }
        }
    }
}