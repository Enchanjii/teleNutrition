<?php

namespace App\Services;

use App\Models\PatientProfile;
use InvalidArgumentException;

class NutritionCalculator
{
    // Physical activity multipliers applied to BMR to estimate TDEE
    public const ACTIVITY_FACTORS = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9,
    ];

    // Share of total energy (AMDR). 55/15/30 falls inside both IOM and PDRI ranges.
    public const CARB_SHARE = 0.55;
    public const PROTEIN_SHARE = 0.15;
    public const FAT_SHARE = 0.30;

    // Energy per gram (Atwater factors)
    public const KCAL_PER_G_CARB = 4;
    public const KCAL_PER_G_PROTEIN = 4;
    public const KCAL_PER_G_FAT = 9;

    public const FIBER_G_PER_1000_KCAL = 14;
    public const SODIUM_LIMIT_MG = 2000; // WHO guideline: < 2,000 mg sodium/day

    /** Basal metabolic rate (kcal/day), Mifflin-St Jeor equation. */
    public function bmr(string $sex, float $weightKg, float $heightCm, int $age): float
    {
        $base = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);

        return $sex === 'male' ? $base + 5 : $base - 161;
    }

    /** Total daily energy expenditure (kcal/day). */
    public function tdee(float $bmr, string $activityLevel): float
    {
        if (!isset(self::ACTIVITY_FACTORS[$activityLevel])) {
            throw new InvalidArgumentException("Unknown activity level: {$activityLevel}");
        }

        return $bmr * self::ACTIVITY_FACTORS[$activityLevel];
    }

    /** Daily macro, fiber and sodium targets for a given calorie goal. */
    public function macroTargets(float $kcal): array
    {
        return [
            'calories' => round($kcal),
            'protein_g' => round($kcal * self::PROTEIN_SHARE / self::KCAL_PER_G_PROTEIN, 1),
            'carbs_g' => round($kcal * self::CARB_SHARE / self::KCAL_PER_G_CARB, 1),
            'fat_g' => round($kcal * self::FAT_SHARE / self::KCAL_PER_G_FAT, 1),
            'fiber_g' => round($kcal / 1000 * self::FIBER_G_PER_1000_KCAL, 1),
            'sodium_mg' => self::SODIUM_LIMIT_MG,
        ];
    }

    /** Full target set for a patient profile. */
    public function targetsFor(PatientProfile $profile): array
    {
        $bmr = $this->bmr(
            $profile->sex,
            (float) $profile->weight_kg,
            (float) $profile->height_cm,
            $profile->birth_date->age
        );
        $tdee = $this->tdee($bmr, $profile->activity_level);

        return ['bmr' => round($bmr), 'tdee' => round($tdee)] + $this->macroTargets($tdee);
    }
}