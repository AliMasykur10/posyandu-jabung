<?php

namespace Tests\Unit;

use App\Models\Measurement;
use PHPUnit\Framework\TestCase;

class MeasurementStatusTest extends TestCase
{
    public function test_weight_status_labels_use_weight_for_age_terminology(): void
    {
        $this->assertSame('Berat Badan Normal', Measurement::STATUS_NORMAL);
        $this->assertSame('Berat Badan Kurang', Measurement::STATUS_UNDERWEIGHT);
        $this->assertSame('Berat Badan Sangat Kurang', Measurement::STATUS_SEVERE_UNDERWEIGHT);
        $this->assertSame('Risiko Berat Badan Lebih', Measurement::STATUS_OVERWEIGHT_RISK);
    }

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
}
