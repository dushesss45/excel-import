<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExcelUploadRequest;
use App\Services\ExcelImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для управления загрузкой и обработкой Excel файлов.
 *
 * Этот контроллер отвечает за отображение формы для загрузки,
 * обработку загружаемых Excel файлов, их валидацию и передачу
 * на обработку в сервисный класс.
 */
class ExcelImportController extends Controller
{
    /**
     * Сервис для обработки Excel файлов.
     *
     * @var ExcelImportService
     */
    protected ExcelImportService $excelImportService;

    /**
     * Конструктор контроллера.
     *
     * @param ExcelImportService $excelImportService Экземпляр сервиса обработки Excel файлов.
     */
    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    /**
     * Отображает HTML-форму для загрузки Excel файла.
     *
     * @return View Шаблон с формой загрузки.
     */
    public function showForm(): View
    {
        // Возвращаем шаблон с формой загрузки
        return view('excel_upload');
    }

    /**
     * Обрабатывает загрузку Excel файла.
     *
     * Этот метод:
     * - Проверяет файл на соответствие требованиям (валидируется через ExcelUploadRequest).
     * - Сохраняет файл во временную директорию.
     * - Передаёт файл в сервисный слой для дальнейшей обработки (например, парсинг, сохранение в БД).
     * - Возвращает ответ клиенту в унифицированном формате JSON.
     *
     * @param ExcelUploadRequest $request Объект запроса с проверенными данными.
     *
     * @return JsonResponse JSON-ответ с результатом обработки.
     */
    public function upload(ExcelUploadRequest $request): JsonResponse
    {
        // Сохраняем файл во временную директорию (storage/app/temp)
        $filePath = $request->file('file')->store('temp');

        // Передаём путь к файлу в сервисный слой для обработки
        $result = $this->excelImportService->handleExcelImport($filePath);

        // Возвращаем унифицированный ответ (метод apiResponse в базовом Controller)
        return $this->apiResponse($result['code'], $result['status'], $result['message']);
    }
}
