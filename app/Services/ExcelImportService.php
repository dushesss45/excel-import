<?php

namespace App\Services;

use App\Jobs\ProcessExcelJob;

/**
 * Сервис для обработки загрузки и импорта Excel файлов.
 *
 * Этот сервис отвечает за обработку загруженных Excel файлов,
 * их сохранение во временное хранилище и постановку задания
 * на обработку в очередь.
 */
class ExcelImportService
{
    /**
     * Обрабатывает загружаемый Excel-файл.
     *
     * Данный метод выполняет:
     * - Сохранение файла во временное хранилище.
     * - Постановку задания на обработку файла в очередь.
     * - Возвращение унифицированного ответа с результатом операции.
     *
     * @param string $filePath Путь к загруженному файлу во временном хранилище.
     *
     * @return array Результат обработки в виде ассоциативного массива
     */
    public function handleExcelImport(string $filePath): array
    {
        // Ставим задание в очередь для обработки файла
        ProcessExcelJob::dispatch($filePath);

        // Возвращаем результат
        return [
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Файл отправлен на импорт.',
        ];
    }
}
