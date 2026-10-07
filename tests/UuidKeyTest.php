<?php

namespace Pmochine\Tests\Report;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;
use Pmochine\Report\Models\Report;
use Pmochine\Tests\Report\Fixtures\UuidPost;
use Pmochine\Tests\Report\Fixtures\UuidUser;

class UuidKeyTest extends AbstractTestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        // The README tells applications with UUID keys to call this before they migrate.
        Builder::morphUsingUuids();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Builder::defaultMorphKeyType('int');
    }

    public function test_the_migration_creates_uuid_morph_columns(): void
    {
        foreach (['reportable_id', 'reporter_id'] as $column) {
            $this->assertNotSame('integer', Schema::getColumnType('reports', $column));
        }

        $this->assertNotSame('integer', Schema::getColumnType('reports_conclusions', 'judge_id'));
    }

    public function test_reports_work_with_uuid_keys(): void
    {
        $post = UuidPost::create(['title' => 'Hello']);
        $ada = UuidUser::create(['name' => 'Ada']);
        $grace = UuidUser::create(['name' => 'Grace']);

        $report = $post->report(['reason' => 'Spam'], $ada);
        $report->conclude(['conclusion' => 'Valid.'], $grace);
        $post->report(['reason' => 'Again'], $grace)->conclude(['conclusion' => 'Valid.'], $grace);

        $fresh = Report::with('reportable', 'reporter', 'conclusion.judge')->find($report->id);

        $this->assertTrue($fresh->reportable->is($post));
        $this->assertTrue($fresh->reporter->is($ada));
        $this->assertTrue($fresh->judge()->is($grace));
        $this->assertTrue($post->isReportedBy($ada));
        $this->assertSame(1, $ada->submittedReports()->count());
        $this->assertCount(1, Report::allJudges());
    }
}
