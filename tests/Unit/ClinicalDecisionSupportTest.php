<?php

namespace Tests\Unit;

use App\Services\ClinicalDecisionSupport;
use PHPUnit\Framework\TestCase;

class ClinicalDecisionSupportTest extends TestCase
{
    private array $targets = [
        'calories' => 2000,
        'protein_g' => 75,
        'fiber_g' => 28,
        'sodium_mg' => 2000,
    ];

    private function intake(array $overrides = []): array
    {
        return array_merge([
            'calories' => 2000,
            'protein_g' => 75,
            'fiber_g' => 28,
            'sodium_mg' => 1500,
        ], $overrides);
    }

    private function terms(array $intake): array
    {
        $flags = (new ClinicalDecisionSupport)->evaluate($this->targets, $intake);

        return array_column($flags, 'term');
    }

    public function test_no_intake_returns_info_flag(): void
    {
        $flags = (new ClinicalDecisionSupport)->evaluate($this->targets, $this->intake(['calories' => 0]));

        $this->assertCount(1, $flags);
        $this->assertSame('info', $flags[0]['severity']);
    }

    public function test_balanced_intake_has_no_flags(): void
    {
        $this->assertSame([], $this->terms($this->intake()));
    }

    public function test_sodium_above_limit_is_flagged_high(): void
    {
        $flags = (new ClinicalDecisionSupport)->evaluate($this->targets, $this->intake(['sodium_mg' => 2455]));

        $this->assertSame('Excessive mineral intake (sodium)', $flags[0]['term']);
        $this->assertSame('high', $flags[0]['severity']);
    }

    public function test_energy_above_and_below_range_is_flagged(): void
    {
        $this->assertContains('Excessive energy intake', $this->terms($this->intake(['calories' => 2500])));
        $this->assertContains('Inadequate energy intake', $this->terms($this->intake(['calories' => 1000])));
    }

    public function test_low_protein_and_fiber_are_flagged(): void
    {
        $terms = $this->terms($this->intake(['protein_g' => 40, 'fiber_g' => 10]));

        $this->assertContains('Inadequate protein intake', $terms);
        $this->assertContains('Inadequate fiber intake', $terms);
    }
}