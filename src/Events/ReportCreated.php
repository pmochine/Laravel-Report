<?php

namespace Pmochine\Report\Events;

use Illuminate\Queue\SerializesModels;
use Pmochine\Report\Models\Report;

/**
 * Dispatched after a new report is saved.
 */
class ReportCreated
{
    use SerializesModels;

    public function __construct(public Report $report)
    {
    }
}
