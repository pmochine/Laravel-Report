<?php

namespace Pmochine\Tests\Report;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Pmochine\Report\Models\Conclusion;
use Pmochine\Report\Models\Report;
use Pmochine\Tests\Report\Fixtures\Member;
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

    public function test_a_conclusion_belongs_to_its_report(): void
    {
        $report = $this->newReport();

        $conclusion = $report->conclude(['conclusion' => 'Valid.'], User::create(['name' => 'Judge']));

        $this->assertTrue($conclusion->fresh()->report->is($report));
    }

    public function test_judge_is_null_without_a_conclusion(): void
    {
        $this->assertNull($this->newReport()->judge());
    }

    public function test_judge_is_up_to_date_after_concluding(): void
    {
        $report = $this->newReport();
        $judge = User::create(['name' => 'Judge']);

        $this->assertNull($report->judge());

        $report->conclude(['conclusion' => 'Valid.'], $judge);

        $this->assertTrue($report->judge()->is($judge));
    }

    public function test_a_judge_with_a_custom_primary_key_is_stored(): void
    {
        $report = $this->newReport();
        Member::create(['name' => 'Filler']);
        $judge = Member::create(['name' => 'Grace']);

        $report->conclude(['conclusion' => 'Valid.'], $judge);

        $this->assertTrue($report->fresh()->judge()->is($judge));
    }

    public function test_the_morph_map_alias_of_the_judge_is_stored(): void
    {
        Relation::enforceMorphMap(['post' => Post::class, 'user' => User::class]);

        $report = $this->newReport();

        $conclusion = $report->conclude(['conclusion' => 'Valid.'], User::create(['name' => 'Judge']));

        $this->assertSame('user', $conclusion->fresh()->judge_type);
    }

    public function test_all_judges_lists_each_judge_once(): void
    {
        $ada = User::create(['name' => 'Ada']);
        $grace = Member::create(['name' => 'Grace']);

        $this->newReport()->conclude(['conclusion' => 'One'], $ada);
        $this->newReport()->conclude(['conclusion' => 'Two'], $ada);
        $this->newReport()->conclude(['conclusion' => 'Three'], $grace);

        $judges = Report::allJudges();

        $this->assertCount(2, $judges);
        $this->assertTrue($judges[0]->is($ada));
        $this->assertTrue($judges[1]->is($grace));
    }

    public function test_all_judges_skips_deleted_judges(): void
    {
        $ada = User::create(['name' => 'Ada']);
        $grace = User::create(['name' => 'Grace']);

        $this->newReport()->conclude(['conclusion' => 'One'], $ada);
        $this->newReport()->conclude(['conclusion' => 'Two'], $grace);
        $grace->delete();

        $judges = Report::allJudges();

        $this->assertCount(1, $judges);
        $this->assertTrue($judges[0]->is($ada));
    }

    public function test_all_judges_does_not_query_once_per_conclusion(): void
    {
        $judges = [User::create(['name' => 'Ada']), User::create(['name' => 'Grace']), User::create(['name' => 'Linus'])];

        foreach ($judges as $judge) {
            $this->newReport()->conclude(['conclusion' => 'Valid.'], $judge);
        }

        DB::enableQueryLog();
        Report::allJudges();

        $this->assertCount(2, DB::getQueryLog());
    }

    public function test_a_judge_type_in_the_data_does_not_change_the_stored_key(): void
    {
        $report = $this->newReport();
        $judge = User::create(['name' => 'Judge']);
        $judge->setAttribute('member_id', 1);

        $conclusion = $report->conclude([
            'conclusion' => 'Valid.',
            'judge_type' => Member::class,
            'judge_id' => 999,
        ], $judge);

        $this->assertSame(User::class, $conclusion->fresh()->judge_type);
        $this->assertSame($judge->id, $conclusion->fresh()->judge_id);
    }

    public function test_an_unknown_judge_type_in_the_data_is_ignored(): void
    {
        $report = $this->newReport();
        $judge = User::create(['name' => 'Judge']);

        $report->conclude(['conclusion' => 'Valid.', 'judge_type' => 'missing-class'], $judge);

        $this->assertTrue($report->fresh()->judge()->is($judge));
    }

    public function test_the_latest_conclusion_wins_in_every_loading_path(): void
    {
        $report = $this->newReport();
        $first = User::create(['name' => 'First']);
        $second = User::create(['name' => 'Second']);

        $report->conclude(['conclusion' => 'One'], $first);
        $report->conclude(['conclusion' => 'Two'], $second);

        $this->assertSame(2, Conclusion::count());
        $this->assertTrue($report->judge()->is($second));
        $this->assertTrue($report->fresh()->judge()->is($second));
        $this->assertTrue(Report::with('conclusion.judge')->find($report->id)->judge()->is($second));
        $this->assertSame('Two', Report::with('conclusion')->get()->first()->conclusion->conclusion);
    }

    public function test_a_cancelled_first_conclusion_leaves_the_report_without_a_judge(): void
    {
        $report = $this->newReport();
        Conclusion::creating(fn () => false);

        $this->assertNull($report->judge());
        $report->conclude(['conclusion' => 'Valid.'], User::create(['name' => 'Judge']));

        $this->assertSame(0, Conclusion::count());
        $this->assertNull($report->judge());
        $this->assertNull($report->fresh()->judge());
    }

    public function test_a_cancelled_second_conclusion_keeps_the_first_judge(): void
    {
        $report = $this->newReport();
        $first = User::create(['name' => 'First']);
        $report->conclude(['conclusion' => 'One'], $first);

        Conclusion::creating(fn () => false);
        $report->conclude(['conclusion' => 'Two'], User::create(['name' => 'Second']));

        $this->assertSame(1, Conclusion::count());
        $this->assertTrue($report->judge()->is($first));
        $this->assertTrue($report->fresh()->judge()->is($first));
    }
}
