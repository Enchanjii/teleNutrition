<?php

namespace Tests\Unit;

use App\Services\Privacy;
use PHPUnit\Framework\TestCase;

class PrivacyTest extends TestCase
{
    public function test_pseudonym_is_short_and_stable(): void
    {
        $privacy = new Privacy;
        $id = '3f2b8c1e-9d4a-4e6b-8a7c-1d2e3f4a5b6c';

        $this->assertSame('PT-3F2B8C1E', $privacy->pseudonym($id));
        $this->assertSame($privacy->pseudonym($id), $privacy->pseudonym($id));
    }

    public function test_age_is_generalized_into_bands(): void
    {
        $privacy = new Privacy;

        $this->assertSame('0-9', $privacy->ageBand(4));
        $this->assertSame('30-39', $privacy->ageBand(34));
        $this->assertSame('80-89', $privacy->ageBand(89));
        $this->assertSame('90+', $privacy->ageBand(90));
        $this->assertSame('90+', $privacy->ageBand(104));
    }
}