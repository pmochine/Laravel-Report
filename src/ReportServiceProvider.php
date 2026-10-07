<?php

/*
 * This file is part of Laravel Reportable.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pmochine\Report;

use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/2016_11_04_000000_create_reports_table.php' => $this->migrationPath(),
            ], 'migrations');
        }
    }

    /**
     * Reuse the file name of a published migration, so that publishing again does not add a second
     * migration for the same tables. Version 3.x published the file without a date prefix.
     */
    protected function migrationPath(): string
    {
        foreach (glob(database_path('migrations/*create_reports_table.php')) ?: [] as $path) {
            if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6}_)?create_reports_table\.php$/', basename($path))) {
                return $path;
            }
        }

        return database_path('migrations/' . now()->format('Y_m_d_His') . '_create_reports_table.php');
    }
}
