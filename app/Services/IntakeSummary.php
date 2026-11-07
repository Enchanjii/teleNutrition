<?php

namespace App\Services;

use App\Models\MealLog;
use Carbon\CarbonInterface;

class IntakeSummary
{
    public const NUTRIENTS = ['calories', 'protein_g', 'carbs_g', 'fat_g', 'fiber_g', 'sodium_mg'];

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
}