<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Row;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для управления данными из таблицы "rows".
 *
 * Этот контроллер обрабатывает запросы для получения данных,
 * сгруппированных по дате, и возвращает их в формате JSON.
 */
class RowsController extends Controller
{
    /**
     * Возвращает список всех записей, сгруппированных по дате.
     *
     * Метод выполняет:
     * - Получение всех записей из таблицы "rows".
     * - Группировку записей по дате.
     * - Форматирование данных, чтобы каждая дата содержала массив записей с полями:
     *   - `excel_id` (ID из Excel файла).
     *   - `name` (Имя).
     *   - `date` (Дата в формате d.m.Y).
     * - Возвращение результата в формате JSON.
     *
     * @return JsonResponse JSON-ответ с данными, сгруппированными по дате.
     */
    public function index(): JsonResponse
    {
        // Получим все записи и сгруппируем их по дате
        $all = Row::all()->groupBy('date');

        // groupBy вернёт коллекцию, где ключ — это значение date, а значение — коллекция записей за этот день
        $result = [];

        foreach ($all as $date => $items) {
            // date — ключ, items — коллекция записей
            // Преобразуем коллекцию записей в массив для каждой даты
            $result[$date] = $items->map(function ($row) {
                return [
                    'excel_id' => $row->excel_id,
                    'name'     => $row->name,
                    'date'     => Carbon::parse($row->date)->format('d.m.Y'),
                ];
            })->toArray();
        }

        // Возвращаем результат в формате JSON
        return response()->json($result);
    }
}
