<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\ParentDetail;
use App\Models\Posyandu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'orangtua') {
            return $this->parentDashboard($user);
        }

        $dashboardData = $this->operationalDashboardData($user);

        if ($user->role === 'admin') {
            return view('admin.dashboard', array_merge($dashboardData, [
                'totalPosyandu' => Posyandu::count(),
                'totalKader' => User::where('role', 'kader')->count(),
            ]));
        }

        return view('dashboard', array_merge($dashboardData, [
            'isKader' => $user->role === 'kader',
            'dashboardTitle' => $user->role === 'bidan'
                ? 'Dashboard Pemantauan Bidan'
                : 'Dashboard Kader',
        ]));
    }

    private function operationalDashboardData(User $user): array
    {
        $month = now()->startOfMonth();
        $childrenQuery = $this->childrenFor($user);
        $totalChildren = (clone $childrenQuery)->count();
        $totalFamilies = $this->familiesFor($user)->count();
        $latestMeasurements = $this->latestMeasurementsForMonth($user, $month);
        $measuredChildren = $latestMeasurements->count();
        $statusSummary = $this->statusSummary($latestMeasurements);

        $unmeasuredChildren = (clone $childrenQuery)
            ->with(['parent', 'posyandu'])
            ->whereDoesntHave('measurements', function (Builder $query) use ($month) {
                $query->whereBetween('measurement_date', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ]);
            })
            ->orderBy('name')
            ->get();

        $riskChildren = $latestMeasurements
            ->filter(fn(Measurement $measurement) => Measurement::isAtRisk($measurement->status))
            ->sortBy(fn(Measurement $measurement) => Measurement::normalizeStatus($measurement->status) === Measurement::STATUS_SEVERE_UNDERWEIGHT ? 0 : 1)
            ->values();

        $recentMeasurementsQuery = Measurement::with(['child.parent', 'child.posyandu'])
            ->orderByDesc('measurement_date')
            ->orderByDesc('id');

        $this->scopeMeasurements($recentMeasurementsQuery, $user);

        return [
            'periodLabel' => $month->translatedFormat('F Y'),
            'totalChildren' => $totalChildren,
            'totalFamilies' => $totalFamilies,
            'measuredChildren' => $measuredChildren,
            'unmeasuredCount' => max($totalChildren - $measuredChildren, 0),
            'coveragePercentage' => $totalChildren > 0
                ? round(($measuredChildren / $totalChildren) * 100, 1)
                : 0,
            'statusSummary' => $statusSummary,
            'riskChildren' => $riskChildren,
            'unmeasuredChildren' => $unmeasuredChildren,
            'recentMeasurements' => $recentMeasurementsQuery->limit(5)->get(),
            'trend' => $this->sixMonthTrend($user),
            'posyanduComparison' => $this->posyanduComparison($user, $latestMeasurements),
        ];
    }

    private function parentDashboard(User $user)
    {
        $parent = ParentDetail::where('user_id', $user->id)->first();
        $myChildren = $parent
            ? Child::with(['measurements' => fn($query) => $query
                ->orderBy('measurement_date')
                ->orderBy('id')])
                ->where('parent_id', $parent->id)
                ->orderBy('name')
                ->get()
            : collect();

        $childrenData = $myChildren->map(function (Child $child) {
            $latest = $child->measurements->last();

            return [
                'child' => $child,
                'ageMonths' => max(0, (int) floor(Carbon::parse($child->birth_date)->diffInMonths(now()))),
                'latest' => $latest,
                'normalizedStatus' => Measurement::normalizeStatus($latest?->status),
                'chart' => [
                    'labels' => $child->measurements
                        ->map(fn(Measurement $measurement) => Carbon::parse($measurement->measurement_date)->format('d/m/Y'))
                        ->values(),
                    'weights' => $child->measurements->pluck('weight')->map(fn($value) => (float) $value)->values(),
                    'heights' => $child->measurements->pluck('height')->map(fn($value) => (float) $value)->values(),
                ],
            ];
        });

        return view('parents.dashboard', compact('childrenData'));
    }

    private function childrenFor(User $user): Builder
    {
        return Child::query()
            ->when($user->role === 'kader', fn(Builder $query) => $query->where('posyandu_id', $user->posyandu_id));
    }

    private function familiesFor(User $user): Builder
    {
        return ParentDetail::query()
            ->when($user->role === 'kader', fn(Builder $query) => $query->where('posyandu_id', $user->posyandu_id));
    }

    private function latestMeasurementsForMonth(User $user, Carbon $month): Collection
    {
        $query = Measurement::with(['child.parent', 'child.posyandu'])
            ->whereBetween('measurement_date', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderByDesc('measurement_date')
            ->orderByDesc('id');

        $this->scopeMeasurements($query, $user);

        return $query->get()->unique('child_id')->values();
    }

    private function scopeMeasurements(Builder $query, User $user): void
    {
        if ($user->role === 'kader') {
            $query->whereHas('child', fn(Builder $childQuery) => $childQuery
                ->where('posyandu_id', $user->posyandu_id));
        }
    }

    private function statusSummary(Collection $measurements): array
    {
        $summary = [
            'normal' => 0,
            'underweight' => 0,
            'severeUnderweight' => 0,
            'overweightRisk' => 0,
            'unknown' => 0,
        ];

        foreach ($measurements as $measurement) {
            $key = match (Measurement::normalizeStatus($measurement->status)) {
                Measurement::STATUS_NORMAL => 'normal',
                Measurement::STATUS_UNDERWEIGHT => 'underweight',
                Measurement::STATUS_SEVERE_UNDERWEIGHT => 'severeUnderweight',
                Measurement::STATUS_OVERWEIGHT_RISK => 'overweightRisk',
                default => 'unknown',
            };

            $summary[$key]++;
        }

        return $summary;
    }

    private function sixMonthTrend(User $user): array
    {
        $months = collect(range(5, 0))->map(fn(int $offset) => now()
            ->startOfMonth()
            ->subMonths($offset));

        $query = Measurement::query()
            ->whereBetween('measurement_date', [
                $months->first()->copy()->startOfMonth(),
                $months->last()->copy()->endOfMonth(),
            ])
            ->orderByDesc('measurement_date')
            ->orderByDesc('id');

        $this->scopeMeasurements($query, $user);

        $measurementsByMonth = $query->get()
            ->groupBy(fn(Measurement $measurement) => Carbon::parse($measurement->measurement_date)->format('Y-m'));

        $trend = [
            'labels' => [],
            'normal' => [],
            'underweight' => [],
            'severeUnderweight' => [],
            'overweightRisk' => [],
        ];

        foreach ($months as $month) {
            $latest = $measurementsByMonth
                ->get($month->format('Y-m'), collect())
                ->unique('child_id')
                ->values();
            $summary = $this->statusSummary($latest);

            $trend['labels'][] = $month->translatedFormat('M Y');
            $trend['normal'][] = $summary['normal'];
            $trend['underweight'][] = $summary['underweight'];
            $trend['severeUnderweight'][] = $summary['severeUnderweight'];
            $trend['overweightRisk'][] = $summary['overweightRisk'];
        }

        return $trend;
    }

    private function posyanduComparison(User $user, Collection $latestMeasurements): Collection
    {
        $posyandus = Posyandu::withCount(['children', 'parents'])
            ->when($user->role === 'kader', fn(Builder $query) => $query->whereKey($user->posyandu_id))
            ->orderBy('name')
            ->get();

        return $posyandus->map(function (Posyandu $posyandu) use ($latestMeasurements) {
            $measurements = $latestMeasurements->filter(
                fn(Measurement $measurement) => (int) $measurement->child->posyandu_id === (int) $posyandu->id
            );

            return [
                'id' => $posyandu->id,
                'name' => $posyandu->name,
                'childrenCount' => $posyandu->children_count,
                'familiesCount' => $posyandu->parents_count,
                'measuredCount' => $measurements->count(),
                'riskCount' => $measurements
                    ->filter(fn(Measurement $measurement) => Measurement::isAtRisk($measurement->status))
                    ->count(),
            ];
        });
    }
}
