<?php

namespace Pmochine\Report\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Pmochine\Report\Models\Report;

trait SubmitsReports
{
    public function submittedReports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reporter');
    }
}
