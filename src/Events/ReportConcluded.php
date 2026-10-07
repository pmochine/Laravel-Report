<?php

namespace Pmochine\Report\Events;

use Pmochine\Report\Models\Conclusion;

/**
 * Dispatched after a new conclusion is saved. The report is $conclusion->report.
 */
class ReportConcluded
{
    public function __construct(public Conclusion $conclusion)
    {
    }
}
