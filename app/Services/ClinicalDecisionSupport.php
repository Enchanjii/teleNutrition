<?php

namespace App\Services;

class ClinicalDecisionSupport
{
    // Ratios of intake to target. Adjust to match your course / PDRI references.
    public const ENERGY_HIGH_RATIO = 1.10;
    public const ENERGY_LOW_RATIO = 0.75;
    public const PROTEIN_LOW_RATIO = 0.80;
    public const FIBER_LOW_RATIO = 0.70;

    /**
     * @param  array  $targets  from NutritionCalculator::targetsFor()
     * @param  array  $intake   from IntakeSummary::forDay()
     * @return array<int, array{severity: string, term: string, message: string}>
     */
    public function evaluate(array $targets, array $intake): array
    {
        if ($intake['calories'] <= 0) {
            return [$this->flag('info', 'No intake recorded', 'No meals were logged for this day.')];
        }

        $flags = [];

        if ($intake['sodium_mg'] > $targets['sodium_mg']) {
            $flags[] = $this->flag(
                'high',
                'Excessive mineral intake (sodium)',
                sprintf(
                    'Intake of %s mg exceeds the %s mg daily limit.',
                    number_format($intake['sodium_mg']),
                    number_format($targets['sodium_mg'])
                )
            );
        }

        $energyRatio = $intake['calories'] / $targets['calories'];
        if ($energyRatio > self::ENERGY_HIGH_RATIO) {
            $flags[] = $this->flag('moderate', 'Excessive energy intake', $this->energyMessage($intake, $targets));
        } elseif ($energyRatio < self::ENERGY_LOW_RATIO) {
            $flags[] = $this->flag('moderate', 'Inadequate energy intake', $this->energyMessage($intake, $targets));
        }

        if ($intake['protein_g'] < $targets['protein_g'] * self::PROTEIN_LOW_RATIO) {
            $flags[] = $this->flag(
                'moderate',
                'Inadequate protein intake',
                sprintf('Intake of %s g is below the %s g daily target.', $intake['protein_g'], $targets['protein_g'])
            );
        }

        if ($intake['fiber_g'] < $targets['fiber_g'] * self::FIBER_LOW_RATIO) {
            $flags[] = $this->flag(
                'moderate',
                'Inadequate fiber intake',
                sprintf('Intake of %s g is below the %s g daily target.', $intake['fiber_g'], $targets['fiber_g'])
            );
        }

        return $flags;
    }

    private function energyMessage(array $intake, array $targets): string
    {
        return sprintf(
            'Energy intake is %d%% of the estimated need (%s of %s kcal).',
            round($intake['calories'] / $targets['calories'] * 100),
            number_format($intake['calories']),
            number_format($targets['calories'])
        );
    }

    private function flag(string $severity, string $term, string $message): array
    {
        return compact('severity', 'term', 'message');
    }
}