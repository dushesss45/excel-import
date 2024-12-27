<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rows', function (Blueprint $table) {
            $table->id()
                ->comment('Первичный ключ таблицы, автоинкрементируемый ID.');

            $table->unsignedBigInteger('excel_id')
                ->index()
                ->unique()
                ->comment('Уникальный идентификатор строки из Excel файла, используется для проверки дубликатов.');

            $table->string('name')
                ->comment('Имя из строки Excel, содержащее только буквы английского алфавита и пробелы.');

            $table->date('date')
                ->comment('Дата из строки Excel, в формате Y-m-d.');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rows');
    }
};
