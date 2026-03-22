<?php

namespace App\Providers;

use App\Models\FamilyMember;
use App\Policies\FamilyMemberPolicy;
use App\Policies\HouseholdLinkPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(FamilyMember::class, FamilyMemberPolicy::class);
        Gate::define('manage-household-link', [HouseholdLinkPolicy::class, 'manage']);

        RateLimiter::for('login', function (Request $request) {
            $key = strtolower($request->string('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                $seconds = $headers['Retry-After'] ?? 60;

                return back()
                    ->withInput(['email' => $request->input('email')])
                    ->withErrors([
                        'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
                    ]);
            });
        });
    }
}
