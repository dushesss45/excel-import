<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Тесты для контроллера ExcelUploadController.
 *
 * Данный тестовый класс проверяет:
 * - Доступность формы для загрузки файлов.
 * - Успешную загрузку корректного файла.
 * - Обработку ошибок при загрузке файлов недопустимого формата.
 */
class ExcelUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: Форма загрузки доступна.
     *
     * Проверяет:
     * - Доступность маршрута `excel.upload.form`.
     * - Возвращение корректного HTML-шаблона.
     *
     * @return void
     */
    public function test_show_upload_form(): void
    {
        // Отключение BasicAuthMiddleware для теста
        $this->withoutMiddleware(\App\Http\Middleware\BasicAuthMiddleware::class);

        // Отправка GET-запроса к маршруту формы загрузки
        $response = $this->get(route('excel.upload.form'));

        // Проверяем успешный HTTP-статус и корректный шаблон
        $response->assertStatus(200);
        $response->assertViewIs('excel_upload');
    }

    /**
     * Тест: Успешная загрузка и постановка файла в очередь.
     *
     * Проверяет:
     * - Загрузку корректного файла формата `.xlsx`.
     * - Сохранение файла в хранилище.
     * - Корректный JSON-ответ от API.
     *
     * @return void
     */
    public function test_successful_file_upload(): void
    {
        // Отключение всех посредников
        $this->withoutMiddleware();

        // Используем фейковое хранилище
        Storage::fake('local');

        // Создаём фейковый файл
        $file = UploadedFile::fake()->create('test.xlsx', 1024, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Отправка POST-запроса к маршруту загрузки файла
        $response = $this->post(route('excel.upload'), [
            'file' => $file,
            '_token' => csrf_token(),
        ]);

        // Проверяем успешный HTTP-статус и корректный JSON-ответ
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => 'Файл отправлен на импорт.',
        ]);

        // Проверяем, что файл был сохранён
        Storage::disk('local')->assertExists('temp/' . $file->hashName());
    }

    /**
     * Тест: Загрузка файла недопустимого формата.
     *
     * Проверяет:
     * - Попытку загрузки файла недопустимого формата (например, `.pdf`).
     * - Возвращение ошибок валидации.
     * - Корректный JSON-ответ с сообщением об ошибке.
     *
     * @return void
     */
    public function test_invalid_file_upload_format(): void
    {
        // Отключение всех посредников
        $this->withoutMiddleware();

        // Используем фейковое хранилище
        Storage::fake('local');

        // Создаём фейковый файл недопустимого формата
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

        // Параметры для BasicAuth
        $username = 'admin';
        $password = 'pass';

        // Отправка POST-запроса с заголовком Authorization
        $response = $this->postJson(route('excel.upload'), [
            'file' => $file,
        ], [
            'Authorization' => 'Basic ' . base64_encode("{$username}:{$password}"),
        ]);

        // Проверяем HTTP-статус ошибки валидации
        $response->assertStatus(422);

        // Проверяем фрагмент JSON-ответа
        $response->assertJsonFragment([
            'message' => 'Допустим только формат .xlsx!',
        ]);

        // Проверяем структуру JSON-ответа
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'file',
            ],
        ]);
    }
}
