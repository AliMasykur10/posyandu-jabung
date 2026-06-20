<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\User;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Admin: Memiliki akses penuh ke sistem
        Gate::define('is-admin', fn(User $user) => $user->role === 'admin');

        // 2. Kader: Akses operasional posyandu
        Gate::define('is-kader', fn(User $user) => $user->role === 'kader');

        // 3. Orang Tua: Akses data anak sendiri
        Gate::define('is-parent', fn(User $user) => $user->role === 'orangtua');

        // 4. Bidan/Puskesmas: Akses laporan dan monitoring
        Gate::define('is-bidan', fn(User $user) => $user->role === 'bidan');
        Gate::define('is-nakes', fn(User $user) => $user->role === 'bidan');

        if (! $this->app->runningInConsole() && str_contains(request()->getHost(), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }
    }
}
