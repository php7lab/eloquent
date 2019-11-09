# Конфигурация менеджера соединений

## Обявление файла конфигурации в ENV

Откройте файл `.env`.

Добавьте переменную окружения `ELOQUENT_CONFIG_FILE`

```
ELOQUENT_CONFIG_FILE=config/eloquent/main.yaml
```

## Объявление в Symfony

Откройте конфиг `config/services.yaml`.

Добавьте конфигурацию менеджера соединений:

```
services:
    PhpLab\Eloquent\Db\Helper\Manager:
        arguments:
            $mainConfigFile: '%env(ELOQUENT_CONFIG_FILE)%'
```

## Объявление на чистом PHP

```php
if (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} else {
    // load all the .env files
    (new Dotenv(false))->loadEnv(__DIR__ . '/.env');
}

$eloquentConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
$capsule = new Manager(null, $eloquentConfigFile);
```

После чего, можете делать инъекции или использовать класс напрямую.

## Общий конфиг

```yaml
connection:
    map:
        article_category: art_category
        article_post: art_post
        eq_migration: migration
    defaultConnection: pgsqlServer
    connections:
        mysqlServer:
            driver: mysql
            host: localhost
            database: symfony-on-rails
            username: root
#            map: карту можно объявлять на каждое соединение отдельно
        pgsqlServer:
            driver: pgsql
            host: localhost
            database: symfony-on-rails
            username: postgres
            password: postgres
        sqliteServer:
            driver: sqlite
            database: /var/sqlite/default.sqlite
fixture:
    directory:
        - /src/Fixture
        - /src/Bundle/Article/Domain/Fixture
        - /src/Bundle/User/Fixture
migrate:
    directory:
        - /src/Bundle/Article/Domain/Migration
        - /src/Bundle/User/Migrations
```

Пути:

* `connection.map` - карта алиасов имен таблиц
* `connection.defaultConnection` - имя подключения по умолчанию
* `connection.connections` - подключения к БД
* `fixture.directory` - пути для поиска фикстур
* `migrate.directory` - пути для поиска миграций
