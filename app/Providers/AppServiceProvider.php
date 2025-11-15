<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\RateReport;

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
        // Share recent rate reports with admin views for the sidebar history
        View::composer('admin.*', function ($view) {
            try {
                $reports = RateReport::latest()->take(50)->get();
            } catch (\Throwable $e) {
                $reports = collect();
            }
            $view->with('rateReports', $reports);
        });
    }
}
