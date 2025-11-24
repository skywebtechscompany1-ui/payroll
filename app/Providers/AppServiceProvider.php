<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpers = app_path('helpers.php');

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Reset connection if running in console to clear any aborted transactions from boot-time queries
        if ($this->app->runningInConsole()) {
            // Ensure 'options' is an array to prevent array_diff_key error in Connector.php
            // This handles cases where DATABASE_URL parsing or environment variables inject a string.
            $default = config('database.default');
            $options = config("database.connections.$default.options");
            if (!is_array($options)) {
                config(["database.connections.$default.options" => []]);
            }

            try {
                DB::reconnect();
            } catch (\Exception $e) {
                // Ignore reconnection errors
            }
        }

        if (PHP_MAJOR_VERSION >= 8) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
