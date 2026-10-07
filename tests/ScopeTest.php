<?php

namespace Pmochine\Tests\Report;

use Pmochine\Report\Models\Report;
use Pmochine\Tests\Report\Fixtures\Post;
use Pmochine\Tests\Report\Fixtures\User;

class ScopeTest extends AbstractTestCase
{
    public function test_pending_and_concluded_split_the_reports(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $open = $post->report(['reason' => 'Open'], $user);
        $done = $post->report(['reason' => 'Done'], $user);
        $twice = $post->report(['reason' => 'Twice'], $user);

        $done->conclude(['conclusion' => 'Valid.'], $user);
        $twice->conclude(['conclusion' => 'One'], $user);
        $twice->conclude(['conclusion' => 'Two'], $user);

        $this->assertSame([$open->id], Report::pending()->pluck('id')->all());
        $this->assertSame([$done->id, $twice->id], Report::concluded()->orderBy('id')->pluck('id')->all());
    }

    public function test_the_scopes_work_on_the_reports_of_a_model(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $other = Post::create(['title' => 'Other']);
        $user = User::create(['name' => 'Ada']);

        $post->report(['reason' => 'Open'], $user);
        $post->report(['reason' => 'Done'], $user)->conclude(['conclusion' => 'Valid.'], $user);
        $other->report(['reason' => 'Open elsewhere'], $user);

        $this->assertSame(1, $post->reports()->pending()->count());
        $this->assertSame(1, $post->reports()->concluded()->count());
        $this->assertSame(2, Report::pending()->count());
    }

    public function test_conclusions_returns_every_conclusion_of_a_report(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $report = $post->report(['reason' => 'Spam'], $user);

        $first = $report->conclude(['conclusion' => 'One'], $user);
        $second = $report->conclude(['conclusion' => 'Two'], $user);

        $this->assertSame([$first->id, $second->id], $report->conclusions()->orderBy('id')->pluck('id')->all());
        $this->assertTrue($report->fresh()->conclusion->is($second));
    }
}
