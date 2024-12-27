<?php

namespace Tests\Feature;

use App\Models\Row;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тесты для RowsController.
 *
 * Данный тестовый класс проверяет функциональность контроллера RowsController,
 * включая корректное получение и группировку данных по дате.
 */
class RowsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест получения сгруппированных данных.
     *
     * Проверяет:
     * - Корректное создание тестовых данных в базе.
     * - Группировку данных по дате.
     * - Правильный формат возвращаемого JSON-ответа.
     *
     * @return void
     */
    public function test_get_grouped_data(): void
    {
        // Создаём тестовые записи в базе данных
        Row::factory()->create([
            'excel_id' => 1,
            'name'     => 'John Doe',
            'date'     => '2024-12-01',
        ]);

        Row::factory()->create([
            'excel_id' => 2,
            'name'     => 'Jane Doe',
            'date'     => '2024-12-01',
        ]);

        Row::factory()->create([
            'excel_id' => 3,
            'name'     => 'Sam Smith',
            'date'     => '2024-12-02',
        ]);

        // Отправляем GET-запрос к маршруту, который возвращает сгруппированные данные
        $response = $this->get(route('rows.index'));

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру возвращаемых данных
        $response->assertJson([
            '2024-12-01' => [
                ['excel_id' => 1, 'name' => 'John Doe', 'date' => '01.12.2024'],
                ['excel_id' => 2, 'name' => 'Jane Doe', 'date' => '01.12.2024'],
            ],
            '2024-12-02' => [
                ['excel_id' => 3, 'name' => 'Sam Smith', 'date' => '02.12.2024'],
            ],
        ]);
    }
}
