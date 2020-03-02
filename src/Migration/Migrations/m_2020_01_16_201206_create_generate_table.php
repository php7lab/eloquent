<?php

namespace Migrations;

use Illuminate\Database\Schema\Blueprint;
use PhpLab\Eloquent\Migration\Base\BaseCreateTableMigration;

class m_2020_01_16_201206_create_generate_table extends BaseCreateTableMigration
{

    protected $tableName = 'migration_generate';
    protected $tableComment = '';

    public function tableSchema()
    {
        return function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->comment('Идентификатор');
            $table->integer('category_id')->comment('');
            $table->string('title')->comment('');
            $table->string('author')->comment('');
            $table->boolean('is_archive')->comment('');
            $table->smallInteger('status')->comment('Статус');
            $table->integer('size')->comment('Размер');
            $table->dateTime('created_at')->comment('Время создания');
        };
    }

}
