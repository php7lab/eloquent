# Развертывание базы данных

Зайти в папку `bin`:

    cd vendor/php7lab/eloquent/bin

Выполнить миграции:

    php console db:migrate:up --withConfirm=0

Выполнить иморт демо-данных в БД для разработки:

    php console db:fixture:import --withConfirm=0

Команды выполнятся без подтверждений.
