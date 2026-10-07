<?php

namespace Pmochine\Tests\Report;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Pmochine\Report\ReportServiceProvider;

class ServiceProviderTest extends AbstractTestCase
{
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = sys_get_temp_dir() . '/laravel-report-' . uniqid();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->databasePath);

        parent::tearDown();
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app->useDatabasePath($this->databasePath);
    }

    public function test_the_provider_is_loaded(): void
    {
        $this->assertInstanceOf(ReportServiceProvider::class, $this->app->getProvider(ReportServiceProvider::class));
    }

    public function test_the_migration_is_publishable_under_the_migrations_tag(): void
    {
        $paths = ServiceProvider::pathsToPublish(ReportServiceProvider::class, 'migrations');

        $this->assertCount(1, $paths);
        $this->assertSame(realpath(__DIR__ . '/../database/migrations'), realpath(array_key_first($paths)));
        $this->assertSame($this->databasePath . '/migrations', reset($paths));
    }

    public function test_publishing_gives_the_migration_a_current_timestamp(): void
    {
        $this->travelTo('2026-10-07 12:00:00');

        $this->artisan('vendor:publish', ['--provider' => ReportServiceProvider::class])->assertSuccessful();

        $files = array_map('basename', glob($this->databasePath . '/migrations/*.php'));

        $this->assertCount(1, $files);
        $this->assertMatchesRegularExpression('/^2026_10_07_\d{6}_create_reports_table\.php$/', $files[0]);
    }
}
