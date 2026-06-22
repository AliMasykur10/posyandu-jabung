<?php

use App\Models\Measurement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceStatus(
            ['Gizi Buruk', 'Gizi Buruk (Severely Underweight)', 'Berat Badan Sangat Kurang'],
            Measurement::STATUS_SEVERE_UNDERWEIGHT
        );
        $this->replaceStatus(
            ['Gizi Kurang', 'Gizi Kurang (Underweight)', 'Berat Badan Kurang'],
            Measurement::STATUS_UNDERWEIGHT
        );
        $this->replaceStatus(
            ['Gizi Baik', 'Gizi Baik (Normal)', 'Normal', 'Berat Badan Normal'],
            Measurement::STATUS_NORMAL
        );
        $this->replaceStatus(
            ['Risiko Berat Lebih', 'Risiko Berat Badan Lebih'],
            Measurement::STATUS_OVERWEIGHT_RISK
        );
    }

    public function down(): void
    {
        DB::table('measurements')
            ->where('status', Measurement::STATUS_SEVERE_UNDERWEIGHT)
            ->update(['status' => 'Gizi Buruk']);
        DB::table('measurements')
            ->where('status', Measurement::STATUS_UNDERWEIGHT)
            ->update(['status' => 'Gizi Kurang']);
        DB::table('measurements')
            ->where('status', Measurement::STATUS_NORMAL)
            ->update(['status' => 'Gizi Baik (Normal)']);
        DB::table('measurements')
            ->where('status', Measurement::STATUS_OVERWEIGHT_RISK)
            ->update(['status' => 'Risiko Berat Lebih']);
    }

    private function replaceStatus(array $legacyValues, string $newValue): void
    {
        DB::table('measurements')
            ->whereIn('status', $legacyValues)
            ->update(['status' => $newValue]);
    }
};
