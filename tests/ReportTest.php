<?php

namespace Pmochine\Tests\Report;

use Illuminate\Database\Eloquent\Relations\Relation;
use Pmochine\Report\Models\Report;
use Pmochine\Tests\Report\Fixtures\Member;
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

    public function test_a_reporter_with_a_custom_primary_key_is_stored(): void
    {
        $post = Post::create(['title' => 'Hello']);
        Member::create(['name' => 'Filler']);
        $member = Member::create(['name' => 'Grace']);

        $report = $post->report(['reason' => 'Spam'], $member);

        $this->assertSame($member->member_id, $report->fresh()->reporter_id);
        $this->assertTrue($report->fresh()->reporter->is($member));
    }

    public function test_the_morph_map_alias_of_the_reporter_is_stored(): void
    {
        Relation::enforceMorphMap(['post' => Post::class, 'user' => User::class]);

        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);

        $report = $post->report(['reason' => 'Spam'], $user);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'reportable_type' => 'post',
            'reporter_type' => 'user',
        ]);
        $this->assertTrue(Report::whereMorphedTo('reporter', $user)->first()->is($report));
    }

    public function test_rows_with_class_names_still_load_with_a_morph_map_that_is_not_enforced(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $old = $post->report(['reason' => 'Stored by 3.x'], $user);

        Relation::morphMap(['post' => Post::class, 'user' => User::class]);
        $new = $post->report(['reason' => 'Stored by 4.x'], $user);

        $this->assertSame(User::class, $old->fresh()->reporter_type);
        $this->assertSame('user', $new->fresh()->reporter_type);

        $reports = Report::with('reporter', 'reportable')->orderBy('id')->get();

        $this->assertTrue($reports[0]->reporter->is($user));
        $this->assertTrue($reports[1]->reporter->is($user));
        $this->assertTrue($reports[0]->reportable->is($post));
    }

    public function test_the_reporter_in_the_data_cannot_replace_the_given_reporter(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);

        $report = $post->report(['reason' => 'Spam', 'reporter_id' => 999, 'reporter_type' => Post::class], $user);

        $this->assertTrue($report->fresh()->reporter->is($user));
    }
}
