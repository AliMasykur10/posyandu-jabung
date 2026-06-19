<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\ParentDetail;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_counts_each_child_once_using_latest_monthly_measurement(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $posyandu = Posyandu::create(['name' => 'Posyandu Melati']);
        $parent = $this->createParent($posyandu, '1000000000000001');
        $measuredChild = $this->createChild($parent, 'Alya');
        $this->createChild($parent, 'Bima');

        $this->createMeasurement($measuredChild, now()->startOfMonth()->addDay(), Measurement::STATUS_NORMAL);
        $this->createMeasurement($measuredChild, now()->startOfMonth()->addDays(2), Measurement::STATUS_UNDERWEIGHT);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('totalChildren', 2)
            ->assertViewHas('measuredChildren', 1)
            ->assertViewHas('unmeasuredCount', 1)
            ->assertViewHas('statusSummary', fn(array $summary) => $summary['normal'] === 0
                && $summary['underweight'] === 1);
    }

    public function test_kader_dashboard_only_contains_data_from_their_posyandu(): void
    {
        $firstPosyandu = Posyandu::create(['name' => 'Posyandu Melati']);
        $secondPosyandu = Posyandu::create(['name' => 'Posyandu Mawar']);
        $kader = User::factory()->create([
            'role' => 'kader',
            'posyandu_id' => $firstPosyandu->id,
        ]);

        $firstChild = $this->createChild(
            $this->createParent($firstPosyandu, '1000000000000002'),
            'Alya'
        );
        $secondChild = $this->createChild(
            $this->createParent($secondPosyandu, '1000000000000003'),
            'Bima'
        );
        $this->createMeasurement($firstChild, now(), Measurement::STATUS_UNDERWEIGHT);
        $this->createMeasurement($secondChild, now(), Measurement::STATUS_SEVERE_UNDERWEIGHT);

        $response = $this->actingAs($kader)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('dashboard')
            ->assertViewHas('totalChildren', 1)
            ->assertViewHas('riskChildren', fn($children) => $children->count() === 1
                && $children->first()->child_id === $firstChild->id)
            ->assertViewHas('posyanduComparison', fn($items) => $items->count() === 1
                && $items->first()['id'] === $firstPosyandu->id);
    }

    public function test_parent_dashboard_only_contains_their_children(): void
    {
        $posyandu = Posyandu::create(['name' => 'Posyandu Melati']);
        $parentUser = User::factory()->create([
            'role' => 'orangtua',
            'posyandu_id' => $posyandu->id,
        ]);
        $parent = $this->createParent($posyandu, '1000000000000004', $parentUser);
        $ownChild = $this->createChild($parent, 'Alya');
        $otherChild = $this->createChild(
            $this->createParent($posyandu, '1000000000000005'),
            'Bima'
        );
        $this->createMeasurement($ownChild, now(), Measurement::STATUS_NORMAL);
        $this->createMeasurement($otherChild, now(), Measurement::STATUS_UNDERWEIGHT);

        $response = $this->actingAs($parentUser)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('parents.dashboard')
            ->assertViewHas('childrenData', fn($children) => $children->count() === 1
                && $children->first()['child']->id === $ownChild->id);
    }

    private function createParent(Posyandu $posyandu, string $familyCardNumber, ?User $user = null): ParentDetail
    {
        return ParentDetail::create([
            'user_id' => $user?->id,
            'posyandu_id' => $posyandu->id,
            'no_kk' => $familyCardNumber,
            'mother_name' => 'Ibu '.$familyCardNumber,
            'address' => 'Jabung Sisir',
            'rt' => '001',
            'rw' => '001',
        ]);
    }

    private function createChild(ParentDetail $parent, string $name): Child
    {
        return Child::create([
            'posyandu_id' => $parent->posyandu_id,
            'parent_id' => $parent->id,
            'name' => $name,
            'birth_date' => now()->subMonths(8)->toDateString(),
            'gender' => 'P',
            'birth_weight' => 3.1,
        ]);
    }

    private function createMeasurement(Child $child, $date, string $status): Measurement
    {
        return Measurement::create([
            'child_id' => $child->id,
            'weight' => 7.2,
            'height' => 68,
            'measurement_date' => $date->toDateString(),
            'status' => $status,
        ]);
    }
}
