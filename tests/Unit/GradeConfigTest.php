<?php

namespace Tests\Unit;

use App\Models\GradeConfig;
use PHPUnit\Framework\TestCase;

class GradeConfigTest extends TestCase
{
    private function makeConfig(float $scaleMax = 100, float $passingGrade = 75): GradeConfig
    {
        $config = new GradeConfig;
        $config->scale_max = $scaleMax;
        $config->passing_grade = $passingGrade;

        return $config;
    }

    public function test_letter_a_for_score_above_85_percent(): void
    {
        $config = $this->makeConfig(100);

        $this->assertSame('A', $config->letterFor(85));
        $this->assertSame('A', $config->letterFor(100));
        $this->assertSame('A', $config->letterFor(90));
    }

    public function test_letter_b_for_score_between_70_and_84(): void
    {
        $config = $this->makeConfig(100);

        $this->assertSame('B', $config->letterFor(70));
        $this->assertSame('B', $config->letterFor(84));
    }

    public function test_letter_c_for_score_between_55_and_69(): void
    {
        $config = $this->makeConfig(100);

        $this->assertSame('C', $config->letterFor(55));
        $this->assertSame('C', $config->letterFor(69));
    }

    public function test_letter_d_for_score_between_40_and_54(): void
    {
        $config = $this->makeConfig(100);

        $this->assertSame('D', $config->letterFor(40));
        $this->assertSame('D', $config->letterFor(54));
    }

    public function test_letter_e_for_score_below_40_percent(): void
    {
        $config = $this->makeConfig(100);

        $this->assertSame('E', $config->letterFor(0));
        $this->assertSame('E', $config->letterFor(39));
    }

    public function test_letter_conversion_respects_custom_scale_max(): void
    {
        // scale_max = 10, score 9 = 90% → A
        $config = $this->makeConfig(10);

        $this->assertSame('A', $config->letterFor(9));
        $this->assertSame('B', $config->letterFor(7));
        $this->assertSame('E', $config->letterFor(2));
    }

    public function test_is_passing_returns_true_when_score_meets_passing_grade(): void
    {
        $config = $this->makeConfig(100, 75);

        $this->assertTrue($config->isPassing(75));
        $this->assertTrue($config->isPassing(100));
    }

    public function test_is_passing_returns_false_when_score_below_passing_grade(): void
    {
        $config = $this->makeConfig(100, 75);

        $this->assertFalse($config->isPassing(74));
        $this->assertFalse($config->isPassing(0));
    }
}
