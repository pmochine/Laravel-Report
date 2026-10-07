<?php

namespace Pmochine\Tests\Report;

use Pmochine\Report\Models\Conclusion;
use Pmochine\Report\Models\Report;
use Pmochine\Tests\Report\Fixtures\Post;
use Pmochine\Tests\Report\Fixtures\User;

class ConclusionTest extends AbstractTestCase
{
    private function newReport(): Report
    {
        $post = Post::create(['title' => 'Hello']);

        return $post->report(['reason' => 'Spam'], User::create(['name' => 'Reporter']));
    }

    public function test_a_report_can_be_concluded(): void
    {
        $report = $this->newReport();
        $judge = User::create(['name' => 'Judge']);

        $conclusion = $report->conclude([
            'conclusion' => 'Valid report.',
            'action_taken' => 'Record deleted.',
            'meta' => ['note' => 'checked'],
        ], $judge);

        $this->assertInstanceOf(Conclusion::class, $conclusion);
        $this->assertDatabaseHas('reports_conclusions', [
            'id' => $conclusion->id,
            'report_id' => $report->id,
            'judge_type' => User::class,
            'judge_id' => $judge->id,
            'conclusion' => 'Valid report.',
            'action_taken' => 'Record deleted.',
        ]);

        $fresh = $report->fresh();
        $this->assertTrue($fresh->conclusion->is($conclusion));
        $this->assertSame(['note' => 'checked'], $fresh->conclusion->meta);
        $this->assertTrue($fresh->judge()->is($judge));
    }

    public function test_action_taken_is_optional(): void
    {
        $report = $this->newReport();

        $conclusion = $report->conclude(['conclusion' => 'Not valid.'], User::create(['name' => 'Judge']));

        $this->assertNull($conclusion->fresh()->action_taken);
    }
}
