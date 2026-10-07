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
        $this->assertSame(
            realpath(__DIR__ . '/../database/migrations/2016_11_04_000000_create_reports_table.php'),
            realpath(array_key_first($paths)),
        );
        $this->assertStringStartsWith($this->databasePath . '/migrations/', reset($paths));
    }

    public function test_publishing_gives_the_migration_a_current_timestamp(): void
    {
        $this->publish();

        $files = $this->publishedMigrations();

        $this->assertCount(1, $files);
        $this->assertMatchesRegularExpression('/^\d{4}_\d{2}_\d{2}_\d{6}_create_reports_table\.php$/', $files[0]);
        $this->assertStringStartsWith(now()->format('Y_m_d_'), $files[0]);
    }

    public function test_publishing_twice_keeps_one_migration(): void
    {
        $this->publish();
        $this->bootProviderAgain();
        $this->travel(5)->seconds();
        $this->publish();

        $this->assertCount(1, $this->publishedMigrations());
    }

    public function test_publishing_with_force_replaces_the_published_migration(): void
    {
        $this->publish();
        $first = $this->publishedMigrations();
        file_put_contents($this->databasePath . '/migrations/' . $first[0], '<?php // changed');

        $this->bootProviderAgain();
        $this->travel(5)->seconds();
        $this->publish(['--force' => true]);

        $this->assertSame($first, $this->publishedMigrations());
        $this->assertStringContainsString("Schema::create('reports'", file_get_contents($this->databasePath . '/migrations/' . $first[0]));
    }

    public function test_publishing_skips_a_migration_published_by_version_3(): void
    {
        mkdir($this->databasePath . '/migrations', 0777, true);
        file_put_contents($this->databasePath . '/migrations/create_reports_table.php', '<?php // 3.x');

        $this->bootProviderAgain();
        $this->publish();

        $this->assertSame(['create_reports_table.php'], $this->publishedMigrations());
        $this->assertSame('<?php // 3.x', file_get_contents($this->databasePath . '/migrations/create_reports_table.php'));
    }

    /**
     * A new artisan call boots the provider again, so it sees the published files.
     */
    private function bootProviderAgain(): void
    {
        $this->app->register(ReportServiceProvider::class, true);
    }

    private function publish(array $options = []): void
    {
        $this->artisan('vendor:publish', ['--provider' => ReportServiceProvider::class] + $options)->assertSuccessful();
    }

    private function publishedMigrations(): array
    {
        return array_values(array_map('basename', glob($this->databasePath . '/migrations/*.php')));
    }
}
