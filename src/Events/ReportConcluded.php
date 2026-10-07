<?php

namespace Pmochine\Report\Events;

use Illuminate\Queue\SerializesModels;
use Pmochine\Report\Models\Conclusion;

/**
 * Dispatched after a new conclusion is saved. The report is $conclusion->report.
 */
class ReportConcluded
{
    use SerializesModels;

    public function __construct(public Conclusion $conclusion)
    {
    }
}
