<?php

namespace App\Console\Commands;

use App\Models\Measurement;
use App\Services\AnthropometryCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;

class RecalculateAnthropometry extends Command
{
    protected $signature = 'anthropometry:recalculate {--force : Recalculate rows that already have a calculation version}';

    protected $description = 'Recalculate all child anthropometry results using the active WHO/Kemenkes standard';

    public function handle(AnthropometryCalculator $calculator): int
    {
        $query = Measurement::with('child')->orderBy('id');

        if (! $this->option('force')) {
            $query->whereNull('calculation_version');
        }

        $total = (clone $query)->count();
        $updated = 0;
        $failed = 0;
        $bar = $this->output->createProgressBar($total);

        $query->chunkById(200, function ($measurements) use ($calculator, &$updated, &$failed, $bar) {
            foreach ($measurements as $measurement) {
                $ageDays = (int) round(Carbon::parse($measurement->child->birth_date)
                    ->startOfDay()
                    ->diffInDays(Carbon::parse($measurement->measurement_date)->startOfDay()));
                $method = $measurement->measurement_method
                    ?: ($ageDays < 731 ? Measurement::METHOD_LENGTH : Measurement::METHOD_HEIGHT);

                try {
                    $result = $calculator->calculate(
                        $measurement->child,
                        (float) $measurement->weight,
                        (float) $measurement->height,
                        $method,
                        $measurement->measurement_date
                    );
                    $measurement->update(array_merge($result, ['status' => $result['bb_tb_status']]));
                    $updated++;
                } catch (InvalidArgumentException $exception) {
                    $failed++;
                    $this->newLine();
                    $this->warn("Pengukuran #{$measurement->id}: {$exception->getMessage()}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai: {$updated} diperbarui, {$failed} perlu diperiksa.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
