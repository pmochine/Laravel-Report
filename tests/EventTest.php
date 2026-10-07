<?php

namespace Pmochine\Tests\Report;

use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Event;
use Pmochine\Report\Events\ReportConcluded;
use Pmochine\Report\Events\ReportCreated;
use Pmochine\Report\Models\Conclusion;
use Pmochine\Tests\Report\Fixtures\Post;
use Pmochine\Tests\Report\Fixtures\User;

class EventTest extends AbstractTestCase
{
    public function test_reporting_dispatches_report_created(): void
    {
        $received = [];
        Event::listen(ReportCreated::class, function (ReportCreated $event) use (&$received) {
            $received[] = $event->report;
        });

        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $report = $post->report(['reason' => 'Spam'], $user);

        $this->assertCount(1, $received);
        $this->assertTrue($received[0]->is($report));
        $this->assertTrue($received[0]->reporter->is($user));
        $this->assertTrue($received[0]->reportable->is($post));
    }

    public function test_concluding_dispatches_report_concluded(): void
    {
        Event::fake([ReportCreated::class, ReportConcluded::class]);

        $post = Post::create(['title' => 'Hello']);
        $judge = User::create(['name' => 'Judge']);
        $report = $post->report(['reason' => 'Spam'], User::create(['name' => 'Ada']));
        $conclusion = $report->conclude(['conclusion' => 'Valid.'], $judge);

        Event::assertDispatchedTimes(ReportCreated::class, 1);
        Event::assertDispatchedTimes(ReportConcluded::class, 1);
        Event::assertDispatched(ReportConcluded::class, function (ReportConcluded $event) use ($conclusion, $report, $judge) {
            return $event->conclusion->is($conclusion)
                && $event->conclusion->report->is($report)
                && $event->conclusion->judge->is($judge);
        });
    }

    public function test_updates_do_not_dispatch_the_events_again(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $report = $post->report(['reason' => 'Spam'], $user);
        $conclusion = $report->conclude(['conclusion' => 'Valid.'], $user);

        Event::fake([ReportCreated::class, ReportConcluded::class]);

        $report->update(['reason' => 'Changed']);
        $conclusion->update(['conclusion' => 'Changed']);

        Event::assertNotDispatched(ReportCreated::class);
        Event::assertNotDispatched(ReportConcluded::class);
    }

    public function test_a_cancelled_conclusion_dispatches_no_event(): void
    {
        Event::fake([ReportConcluded::class]);
        Conclusion::creating(fn () => false);

        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $post->report(['reason' => 'Spam'], $user)->conclude(['conclusion' => 'Valid.'], $user);

        $this->assertSame(0, Conclusion::count());
        Event::assertNotDispatched(ReportConcluded::class);
    }

    public function test_a_queued_report_created_listener_gets_a_fresh_report(): void
    {
        $payload = null;
        Event::listen(ReportCreated::class, function (ReportCreated $event) use (&$payload) {
            $payload = $this->queuedPayload($event);
        });

        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $report = $post->report(['reason' => 'Spam'], $user);
        $report->update(['reason' => 'Reviewed']);

        $event = $this->runQueuedPayload($payload);
        $event->report->update(['meta' => ['notified' => true]]);

        $this->assertSame('Reviewed', $report->fresh()->reason);
        $this->assertSame(['notified' => true], $report->fresh()->meta);
    }

    public function test_a_queued_report_concluded_listener_gets_a_fresh_conclusion(): void
    {
        $payload = null;
        Event::listen(ReportConcluded::class, function (ReportConcluded $event) use (&$payload) {
            $payload = $this->queuedPayload($event);
        });

        $post = Post::create(['title' => 'Hello']);
        $user = User::create(['name' => 'Ada']);
        $conclusion = $post->report(['reason' => 'Spam'], $user)->conclude(['conclusion' => 'Valid.'], $user);
        $conclusion->update(['conclusion' => 'Changed by a moderator']);

        $event = $this->runQueuedPayload($payload);
        $event->conclusion->update(['meta' => ['notified' => true]]);

        $this->assertSame('Changed by a moderator', $conclusion->fresh()->conclusion);
        $this->assertSame(['notified' => true], $conclusion->fresh()->meta);
    }

    /**
     * Serialize the event the way Laravel stores a queued listener job.
     */
    private function queuedPayload(object $event): string
    {
        return serialize(new CallQueuedListener('App\\Listeners\\NotifyModerators', 'handle', [$event]));
    }

    private function runQueuedPayload(string $payload): object
    {
        return unserialize($payload)->data[0];
    }
}
