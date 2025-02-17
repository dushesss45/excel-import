<?php

namespace Database\Factories;

use App\Models\Row;
use Illuminate\Database\Eloquent\Factories\Factory;

class RowFactory extends Factory
{
    protected $model = Row::class;

    public function definition(): array
    {
        return [
            'excel_id' => $this->faker->unique()->randomNumber(),
            'name' => $this->faker->name,
            'date' => $this->faker->date('Y-m-d'),
        ];
    }
}
