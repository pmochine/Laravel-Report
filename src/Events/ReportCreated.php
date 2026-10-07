<?php

namespace Pmochine\Report\Events;

use Pmochine\Report\Models\Report;

/**
 * Dispatched after a new report is saved.
 */
class ReportCreated
{
    public function __construct(public Report $report)
    {
    }
}
