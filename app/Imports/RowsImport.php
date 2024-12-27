<?php

namespace App\Imports;

use App\Models\Row;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Импорт данных из Excel в таблицу "rows".
 *
 * Класс реализует:
 * - Чтение данных построчно (чанками) для оптимизации работы.
 * - Валидацию каждой строки.
 * - Запись валидных данных в базу данных.
 * - Логирование ошибок и отслеживание прогресса в Redis.
 */
class RowsImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    /**
     * Redis-ключ для отслеживания прогресса.
     *
     * @var string
     */
    protected string $redisKey;

    /**
     * Ссылочный массив для записи ошибок.
     *
     * @var array
     */
    protected array $errors;

    /**
     * Конструктор импорта.
     *
     * @param string $redisKey Ключ Redis для хранения прогресса.
     * @param array &$errors Ссылочный массив для записи ошибок.
     */
    public function __construct(string $redisKey, array &$errors)
    {
        $this->redisKey = $redisKey;
        $this->errors = &$errors;
    }

    /**
     * Обработка коллекции строк (чанка).
     *
     * @param Collection $rows Коллекция строк из Excel.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // Учитываем, что первая строка - шапка.
            $excelId = $row['id'] ?? null;
            $name = $row['name'] ?? null;
            $date = $row['date'] ?? null;

            $lineErrors = [];

            // Валидация ID
            if (!ctype_digit(strval($excelId))) {
                $lineErrors[] = "Некорректный id {$excelId}";
            }

            // Валидация имени
            if (!preg_match('/^[A-Za-z ]+$/', trim($name ?? ''))) {
                $lineErrors[] = "Некорректное имя {$name}";
            }

            // Валидация даты
            $carbonDate = null;
            if ($date) {
                try {
                    $carbonDate = Carbon::createFromFormat('d.m.Y', $date);
                    if (!$carbonDate->isValid()) {
                        $lineErrors[] = "Несуществующая дата {$date}";
                    }
                } catch (\Exception $e) {
                    $lineErrors[] = "Некорректный формат даты {$date}";
                }
            } else {
                $lineErrors[] = 'Пустая дата';
            }

            // Проверка на дубликат ID
            if (empty($lineErrors) && $excelId) {
                $exists = Row::where('excel_id', $excelId)->exists();
                if ($exists) {
                    $lineErrors[] = "Дубликат ID {$excelId}";
                }
            }

            // Если есть ошибки, записываем их и пропускаем строку.
            if (!empty($lineErrors)) {
                $this->errors[$lineNumber] = $lineErrors;
                continue;
            }

            // Если ошибок нет, сохраняем данные в базу.
            Row::create([
                'excel_id' => $excelId,
                'name' => $name,
                'date' => $carbonDate->format('Y-m-d'),
            ]);
        }

        // Обновляем прогресс в Redis.
        $current = Redis::get($this->redisKey) ?? 0;
        Redis::set($this->redisKey, $current + $rows->count());
    }

    /**
     * Размер чанка для обработки.
     *
     * @return int Размер чанка.
     */
    public function chunkSize(): int
    {
        return 2000; // По умолчанию 2000 строк на чанк.
    }
}
