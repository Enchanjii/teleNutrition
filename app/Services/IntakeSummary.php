<?php

namespace App\Services;

use App\Models\MealLog;
use Carbon\CarbonInterface;

class IntakeSummary
{
    public const NUTRIENTS = ['calories', 'protein_g', 'carbs_g', 'fat_g', 'fiber_g', 'sodium_mg'];

    public const LABELS = [
        'calories' => ['Calories', 'kcal'],
        'protein_g' => ['Protein', 'g'],
        'carbs_g' => ['Carbohydrates', 'g'],
        'fat_g' => ['Fat', 'g'],
        'fiber_g' => ['Fiber', 'g'],
        'sodium_mg' => ['Sodium (limit)', 'mg'],
    ];

    /** Total nutrients a patient ate on a given day (food values x servings). */
    public function forDay(int $userId, CarbonInterface $date): array
    {
        $totals = array_fill_keys(self::NUTRIENTS, 0.0);

        $logs = MealLog::with('food')
            ->where('user_id', $userId)
            ->whereDate('logged_at', $date)
            ->get();

        foreach ($logs as $log) {
            foreach (self::NUTRIENTS as $nutrient) {
                $totals[$nutrient] += (float) $log->food->{$nutrient} * (float) $log->quantity;
            }
        }

        return array_map(fn($value) => round($value, 1), $totals);
    }

    /** Rows of intake vs target (with percent of target) for display. */
    public function compare(array $targets, array $intake): array
    {
        $rows = [];

        foreach (self::LABELS as $key => [$label, $unit]) {
            $rows[] = [
                'label' => $label,
                'unit' => $unit,
                'intake' => $intake[$key],
                'target' => $targets[$key],
                'percent' => $targets[$key] > 0 ? round($intake[$key] / $targets[$key] * 100) : 0,
            ];
        }

        return $rows;
    }
}