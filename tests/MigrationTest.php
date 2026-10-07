<?php

namespace Pmochine\Tests\Report;

use Illuminate\Support\Facades\Schema;

class MigrationTest extends AbstractTestCase
{
    public function test_the_migration_creates_both_tables(): void
    {
        $this->assertTrue(Schema::hasColumns('reports', [
            'id', 'reportable_type', 'reportable_id', 'reporter_type', 'reporter_id', 'reason', 'meta', 'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('reports_conclusions', [
            'id', 'report_id', 'judge_type', 'judge_id', 'conclusion', 'action_taken', 'meta', 'created_at', 'updated_at',
        ]));
    }

    public function test_the_migration_can_be_rolled_back(): void
    {
        $migration = require __DIR__ . '/../database/migrations/2016_11_04_000000_create_reports_table.php';

        $migration->down();

        $this->assertFalse(Schema::hasTable('reports'));
        $this->assertFalse(Schema::hasTable('reports_conclusions'));
    }
}
