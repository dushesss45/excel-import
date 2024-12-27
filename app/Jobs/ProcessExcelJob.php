<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RowsImport;

/**
 * Задание для обработки Excel файла.
 *
 * Этот Job выполняет обработку загруженного Excel файла:
 * - Отслеживает прогресс импорта через Redis.
 * - Логирует ошибки обработки строк.
 * - Сохраняет ошибки в файл.
 */
class ProcessExcelJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Путь к файлу Excel.
     *
     * @var string
     */
    protected string $path;

    /**
     * Ключ для отслеживания прогресса в Redis.
     *
     * @var string
     */
    protected string $redisKey;

    /**
     * Путь к файлу с результатами ошибок.
     *
     * @var string
     */
    protected string $resultFile;

    /**
     * Конструктор Job.
     *
     * @param string $path Путь к Excel файлу.
     * @param string $redisKey Ключ для Redis (по умолчанию 'excel_import_progress').
     * @param string $resultFile Путь к файлу для сохранения ошибок (по умолчанию 'storage/result.txt').
     */
    public function __construct(string $path, string $redisKey = 'excel_import_progress', string $resultFile = 'storage/result.txt')
    {
        $this->path = $path;
        $this->redisKey = $redisKey;
        $this->resultFile = $resultFile;
    }

    /**
     * Выполнение логики Job.
     *
     * Логика включает:
     * - Обнуление прогресса в Redis.
     * - Импорт данных из Excel.
     * - Логирование ошибок в Redis и файл.
     * - Установка статуса завершения импорта.
     */
    public function handle(): void
    {
        // Инициализируем прогресс в Redis
        Redis::set($this->redisKey . '_progress', 0);

        // Массив для ошибок
        $errors = [];

        // Импорт данных через RowsImport
        Excel::import(new RowsImport($this->redisKey . '_progress', $errors), $this->path);

        // Если есть ошибки, сохраняем их в файл
        if (!empty($errors)) {
            $content = "";
            foreach ($errors as $line => $lineErrors) {
                $content .= "Строка {$line}: " . implode(', ', $lineErrors) . "\n";
            }
            Storage::put($this->resultFile, $content);
        }

        // Устанавливаем статус завершения
        $this->ensureHashKey($this->redisKey . '_status');
        Redis::hset($this->redisKey . '_status', 'finished', true);
    }

    /**
     * Проверяет наличие ключа Redis и его тип.
     *
     * Если ключ существует, но его тип не является "hash", он удаляется.
     *
     * @param string $key Ключ в Redis.
     */
    private function ensureHashKey(string $key): void
    {
        if (Redis::exists($key) && Redis::type($key) !== 'hash') {
            Redis::del($key);
        }
    }
}
