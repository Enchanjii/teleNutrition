<?php

namespace Tests\Unit;

use App\Services\NutritionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NutritionCalculatorTest extends TestCase
{
    public function test_bmr_for_male_uses_mifflin_st_jeor(): void
    {
        // 10*70 + 6.25*175 - 5*25 + 5 = 1673.75
        $this->assertEqualsWithDelta(1673.75, (new NutritionCalculator)->bmr('male', 70, 175, 25), 0.01);
    }

    public function test_bmr_for_female_uses_mifflin_st_jeor(): void
    {
        // 10*70 + 6.25*175 - 5*25 - 161 = 1507.75
        $this->assertEqualsWithDelta(1507.75, (new NutritionCalculator)->bmr('female', 70, 175, 25), 0.01);
    }

    public function test_tdee_applies_activity_factor(): void
    {
        $this->assertEqualsWithDelta(2008.5, (new NutritionCalculator)->tdee(1673.75, 'sedentary'), 0.01);
    }

    public function test_unknown_activity_level_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new NutritionCalculator)->tdee(1600, 'couch_potato');
    }

    public function test_macro_targets_for_2000_kcal(): void
    {
        $targets = (new NutritionCalculator)->macroTargets(2000);

        $this->assertSame(2000.0, $targets['calories']);
        $this->assertSame(75.0, $targets['protein_g']);
        $this->assertSame(275.0, $targets['carbs_g']);
        $this->assertSame(66.7, $targets['fat_g']);
        $this->assertSame(28.0, $targets['fiber_g']);
        $this->assertSame(2000, $targets['sodium_mg']);
    }
}