<?php

namespace Pmochine\Tests\Report;

use Pmochine\Tests\Report\Fixtures\Post;
use Pmochine\Tests\Report\Fixtures\User;

class ReportTest extends AbstractTestCase
{
    public function test_a_model_can_be_reported(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);

        $report = $post->report(['reason' => 'Spam', 'meta' => ['note' => 'first']], $user);

        $this->assertTrue($report->exists);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reporter_type' => User::class,
            'reporter_id' => $user->id,
            'reason' => 'Spam',
        ]);
        $this->assertSame(['note' => 'first'], $report->fresh()->meta);
        $this->assertTrue($post->reports()->first()->is($report));
        $this->assertTrue($report->fresh()->reportable->is($post));
    }

    public function test_a_report_returns_its_reporter(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);

        $report = $post->report(['reason' => 'Spam'], $user);

        $this->assertTrue($report->fresh()->reporter->is($user));
    }
}
