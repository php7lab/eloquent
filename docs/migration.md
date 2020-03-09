# Миграции

Пример миграции для создания таблицы:

```php
namespace Migrations;

use Illuminate\Database\Schema\Blueprint;
use PhpLab\Eloquent\Migration\Base\BaseCreateTableMigration;
use PhpLab\Eloquent\Migration\Enums\ForeignActionEnum;

if (!class_exists(m_2014_10_12_200000_create_post_table::class)) {

    class m_2014_10_12_200000_create_post_table extends BaseCreateTableMigration
    {

        protected $tableName = 'article_post';
        protected $tableComment = 'Статьи';

        public function tableSchema()
        {
            return function (Blueprint $table) {
                $table->integer('id')->autoIncrement()->comment('Идентификатор');
                $table->integer('category_id')->comment('ID категории');
                $table->string('title')->comment('Заголовок статьи');
                $table->dateTime('created_at');
                $table
                    ->foreign('category_id')
                    ->references('id')
                    ->on($this->encodeTableName('article_category'))
                    ->onDelete(ForeignActionEnum::CASCADE)
                    ->onUpdate(ForeignActionEnum::CASCADE);
            };
        }

    }

}
```

## Консоль

Переходим в папку `bin`:

    cd vendor/php7lab/eloquent/bin

Применить миграции:

    php console db:migrate:up

Откатить миграции:

    php console db:migrate:down

