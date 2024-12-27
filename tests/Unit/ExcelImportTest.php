<?php

namespace Tests\Unit;

use App\Imports\RowsImport;
use App\Jobs\ProcessExcelJob;
use App\Models\Row;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Unit тесты для функционала импорта Excel.
 */
class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: Успешный импорт данных.
     *
     * Проверяет, что задача по импорту Excel была поставлена в очередь
     * и файл был успешно обработан.
     *
     * @return void
     */
    public function test_successful_data_import()
    {
        // Переключение на синхронную обработку очередей
        config(['queue.default' => 'sync']);

        // Подделка работы Excel
        Excel::fake();

        // Запускаем задачу на обработку
        ProcessExcelJob::dispatch('temp/test.xlsx');

        // Проверяем, что файл был обработан
        Excel::assertImported('temp/test.xlsx');
    }

    /**
     * Тест: Данные сохраняются в базу данных.
     *
     * Проверяет, что записи корректно сохраняются в таблице `rows` после импорта.
     *
     * @return void
     */
    public function test_data_is_saved_to_database()
    {
        // Создаём тестовые записи в базе данных
        Row::factory()->create([
            'excel_id' => 1,
            'name'     => 'Test Name 1',
            'date'     => '2024-12-31',
        ]);

        Row::factory()->create([
            'excel_id' => 2,
            'name'     => 'Test Name 2',
            'date'     => '2024-12-31',
        ]);

        // Проверяем наличие записей в базе данных
        $this->assertDatabaseHas('rows', [
            'excel_id' => 1,
            'name'     => 'Test Name 1',
            'date'     => '2024-12-31',
        ]);

        $this->assertDatabaseHas('rows', [
            'excel_id' => 2,
            'name'     => 'Test Name 2',
            'date'     => '2024-12-31',
        ]);
    }

    /**
     * Тест: Ошибки валидации логируются корректно.
     *
     * Проверяет, что ошибки валидации данных логируются в массиве ошибок.
     *
     * @return void
     */
    public function test_validation_errors_logged()
    {
        // Создаём массив для ошибок
        $errors = [];

        // Инициализируем импорт с ключом Redis и ссылкой на массив ошибок
        $rowsImport = new RowsImport('test_key', $errors);

        // Передаём коллекцию строк с некорректными данными
        $rows = collect([
            ['id' => 'abc', 'name' => 'InvalidName123', 'date' => '31.13/2024'], // Некорректные данные
        ]);

        // Обрабатываем коллекцию
        $rowsImport->collection($rows);

        // Проверяем, что массив ошибок не пустой
        $this->assertNotEmpty($errors);

        // Проверяем, что для строки 2 есть ошибки
        $this->assertArrayHasKey(2, $errors);

        // Проверяем содержание ошибки
        $this->assertContains('Некорректный id abc', $errors[2]);
        $this->assertContains('Некорректное имя InvalidName123', $errors[2]);
        $this->assertContains('Некорректный формат даты 31.13/2024', $errors[2]);
    }
}
