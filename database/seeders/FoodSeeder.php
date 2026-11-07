<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // [name, kcal, protein_g, carbs_g, fat_g, fiber_g, sodium_mg] per 100 g
        $foods = [
            ['White rice, cooked', 130, 2.7, 28.2, 0.3, 0.4, 1],
            ['Brown rice, cooked', 112, 2.3, 23.5, 0.8, 1.8, 5],
            ['Chicken breast, cooked', 165, 31.0, 0.0, 3.6, 0.0, 74],
            ['Egg, boiled', 155, 12.6, 1.1, 10.6, 0.0, 124],
            ['Tilapia, cooked', 128, 26.2, 0.0, 2.7, 0.0, 56],
            ['Banana', 89, 1.1, 22.8, 0.3, 2.6, 1],
            ['Mango, ripe', 60, 0.8, 15.0, 0.4, 1.6, 1],
            ['Papaya', 43, 0.5, 10.8, 0.3, 1.7, 8],
            ['Kangkong (water spinach), raw', 19, 2.6, 3.1, 0.2, 2.1, 113],
            ['Malunggay leaves, raw', 64, 9.4, 8.2, 1.4, 2.0, 9],
            ['Sweet potato (kamote), boiled', 76, 1.4, 17.7, 0.1, 2.5, 27],
            ['White bread', 265, 9.0, 49.0, 3.2, 2.7, 491],
            ['Milk, whole', 61, 3.2, 4.8, 3.3, 0.0, 43],
            ['Oatmeal, cooked', 71, 2.5, 12.0, 1.5, 1.7, 4],
        ];

        foreach ($foods as [$name, $kcal, $protein, $carbs, $fat, $fiber, $sodium]) {
            Food::updateOrCreate(
                ['name' => $name],
                [
                    'serving_size' => 100,
                    'serving_unit' => 'g',
                    'calories' => $kcal,
                    'protein_g' => $protein,
                    'carbs_g' => $carbs,
                    'fat_g' => $fat,
                    'fiber_g' => $fiber,
                    'sodium_mg' => $sodium,
                ]
            );
        }
    }
}