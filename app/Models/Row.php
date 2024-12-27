<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель для работы с таблицей "rows".
 *
 * Модель предоставляет доступ к данным таблицы "rows" и позволяет
 * работать с данными, импортированными из Excel файла.
 */
class Row extends Model
{
    use HasFactory;

    /**
     * Имя таблицы, связанной с моделью.
     *
     * @var string
     */
    protected $table = 'rows';

    /**
     * Поля, разрешённые для массового заполнения.
     *
     * Эти поля можно заполнять при создании или обновлении записи
     * через методы `create` или `update`.
     *
     * @var array
     */
    protected $fillable = [
        'excel_id', // ID строки из Excel файла
        'name',     // Имя, указанное в строке
        'date',     // Дата в формате Y-m-d
    ];
}
