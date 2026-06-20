<?php

namespace Tests\Unit;

use App\Models\Measurement;
use PHPUnit\Framework\TestCase;

class MeasurementStatusTest extends TestCase
{
    public function test_it_normalizes_legacy_nutrition_status_labels(): void
    {
        $this->assertSame(
            Measurement::STATUS_SEVERE_UNDERWEIGHT,
            Measurement::normalizeStatus('Gizi Buruk (Severely Underweight)')
        );
        $this->assertSame(
            Measurement::STATUS_UNDERWEIGHT,
            Measurement::normalizeStatus('Gizi Kurang (Underweight)')
        );
        $this->assertSame(
            Measurement::STATUS_OVERWEIGHT_RISK,
            Measurement::normalizeStatus('Risiko Berat Badan Lebih')
        );
        $this->assertSame(
            Measurement::STATUS_NORMAL,
            Measurement::normalizeStatus('Normal')
        );
    }

    public function test_only_underweight_statuses_are_marked_as_at_risk(): void
    {
        $this->assertTrue(Measurement::isAtRisk(Measurement::STATUS_UNDERWEIGHT));
        $this->assertTrue(Measurement::isAtRisk(Measurement::STATUS_SEVERE_UNDERWEIGHT));
        $this->assertFalse(Measurement::isAtRisk(Measurement::STATUS_NORMAL));
        $this->assertFalse(Measurement::isAtRisk(Measurement::STATUS_OVERWEIGHT_RISK));
    }

    public function test_priority_risk_includes_both_undernutrition_and_excess_nutrition(): void
    {
        $wasted = new Measurement([
            'bb_tb_status' => Measurement::BB_TB_WASTED,
        ]);
        $obese = new Measurement([
            'bb_tb_status' => Measurement::BB_TB_OBESE,
        ]);
        $healthy = new Measurement([
            'bb_u_status' => Measurement::BB_U_NORMAL,
            'tb_u_status' => Measurement::TB_U_NORMAL,
            'bb_tb_status' => Measurement::BB_TB_NORMAL,
            'imt_u_status' => Measurement::BB_TB_NORMAL,
        ]);

        $this->assertTrue($wasted->hasPriorityRisk());
        $this->assertSame(Measurement::BB_TB_WASTED, $wasted->priorityStatus());
        $this->assertTrue($obese->hasPriorityRisk());
        $this->assertSame(Measurement::BB_TB_OBESE, $obese->priorityStatus());
        $this->assertFalse($healthy->hasPriorityRisk());
        $this->assertNull($healthy->priorityStatus());
    }
}
