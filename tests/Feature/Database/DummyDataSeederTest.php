<?php

namespace Tests\Feature\Database;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\NutritionStandard;
use App\Models\ParentDetail;
use App\Models\Posyandu;
use App\Models\User;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DummyDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_complete_repeatable_data_without_changing_posyandus(): void
    {
        $originalPosyandus = collect([
            ['name' => 'Posyandu Melati', 'address' => 'Alamat manual Melati'],
            ['name' => 'Posyandu Mawar', 'address' => 'Alamat manual Mawar'],
            ['name' => 'Posyandu Kenanga', 'address' => 'Alamat manual Kenanga'],
            ['name' => 'Posyandu Dahlia', 'address' => 'Alamat manual Dahlia'],
            ['name' => 'Posyandu Anggrek', 'address' => 'Alamat manual Anggrek'],
        ])->map(fn(array $attributes) => Posyandu::create($attributes));

        $this->seed(DummyDataSeeder::class);

        $this->assertDatasetCounts();
        $this->assertCurrentMonthDistribution();
        $this->assertDataIntegrity();

        foreach ($originalPosyandus as $original) {
            $current = Posyandu::findOrFail($original->id);

            $this->assertSame($original->name, $current->name);
            $this->assertSame($original->address, $current->address);
        }

        $this->seed(DummyDataSeeder::class);

        $this->assertDatasetCounts();
        $this->assertCurrentMonthDistribution();
        $this->assertSame($originalPosyandus->pluck('id')->all(), Posyandu::orderBy('id')->pluck('id')->all());
    }

    private function assertDatasetCounts(): void
    {
        $this->assertSame(5, Posyandu::count());
        $this->assertSame(57, User::count());
        $this->assertSame(50, ParentDetail::count());
        $this->assertSame(75, Child::count());
        $this->assertSame(435, Measurement::count());
        $this->assertSame(26, NutritionStandard::count());

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(1, User::where('role', 'bidan')->count());
        $this->assertSame(5, User::where('role', 'kader')->count());
        $this->assertSame(50, User::where('role', 'orangtua')->count());
    }

    private function assertCurrentMonthDistribution(): void
    {
        $measurements = Measurement::whereBetween('measurement_date', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->get();

        $this->assertSame(60, $measurements->unique('child_id')->count());
        $this->assertSame(40, $measurements->where('status', Measurement::STATUS_NORMAL)->count());
        $this->assertSame(10, $measurements->where('status', Measurement::STATUS_UNDERWEIGHT)->count());
        $this->assertSame(5, $measurements->where('status', Measurement::STATUS_SEVERE_UNDERWEIGHT)->count());
        $this->assertSame(5, $measurements->where('status', Measurement::STATUS_OVERWEIGHT_RISK)->count());
    }

    private function assertDataIntegrity(): void
    {
        $this->assertSame(0, ParentDetail::whereDoesntHave('user')->count());
        $this->assertSame(50, DB::table('parent_details')->distinct()->count('no_kk'));
        $this->assertSame(50, DB::table('parent_details')->distinct()->count('nik_mother'));
        $this->assertSame(50, DB::table('parent_details')->distinct()->count('nik_father'));

        $childrenWithWrongPosyandu = DB::table('children')
            ->join('parent_details', 'children.parent_id', '=', 'parent_details.id')
            ->whereColumn('children.posyandu_id', '!=', 'parent_details.posyandu_id')
            ->count();
        $measurementsBeforeBirth = DB::table('measurements')
            ->join('children', 'measurements.child_id', '=', 'children.id')
            ->whereColumn('measurements.measurement_date', '<', 'children.birth_date')
            ->count();

        $this->assertSame(0, $childrenWithWrongPosyandu);
        $this->assertSame(0, $measurementsBeforeBirth);
    }
}
