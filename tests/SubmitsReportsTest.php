<?php

namespace Pmochine\Tests\Report;

use Illuminate\Database\Eloquent\Relations\Relation;
use Pmochine\Tests\Report\Fixtures\Member;
use Pmochine\Tests\Report\Fixtures\Post;
use Pmochine\Tests\Report\Fixtures\User;

class SubmitsReportsTest extends AbstractTestCase
{
    public function test_submitted_reports_lists_the_reports_a_model_sent(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $ada = User::create(['name' => 'Ada']);
        $grace = User::create(['name' => 'Grace']);

        $sent = $post->report(['reason' => 'Spam'], $ada);
        $about = $ada->report(['reason' => 'Rude user'], $grace);

        $this->assertSame([$sent->id], $ada->submittedReports()->pluck('id')->all());
        $this->assertSame([$about->id], $ada->reports()->pluck('id')->all());
        $this->assertSame([$about->id], $grace->submittedReports()->pluck('id')->all());
    }

    public function test_submitted_reports_compares_type_and_key(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $member = Member::create(['name' => 'Grace']);

        $this->assertSame($user->getKey(), $member->getKey());

        $post->report(['reason' => 'Spam'], $member);

        $this->assertSame(0, $user->submittedReports()->count());
    }

    public function test_submitted_reports_can_be_counted_and_filtered(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $ada = User::create(['name' => 'Ada']);
        User::create(['name' => 'Grace']);

        $post->report(['reason' => 'One'], $ada);
        $post->report(['reason' => 'Two'], $ada)->conclude(['conclusion' => 'Valid.'], $ada);

        $counts = User::withCount('submittedReports')->orderBy('id')->pluck('submitted_reports_count')->all();

        $this->assertSame([2, 0], $counts);
        $this->assertSame(1, $ada->submittedReports()->pending()->count());
    }

    public function test_submitted_reports_uses_the_morph_map(): void
    {
        Relation::enforceMorphMap(['post' => Post::class, 'user' => User::class]);

        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $report = $post->report(['reason' => 'Spam'], $user);

        $this->assertTrue($user->submittedReports()->first()->is($report));
    }
}
