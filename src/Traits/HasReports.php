<?php

/*
 * This file is part of Laravel Reportable.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pmochine\Report\Traits;

use Pmochine\Report\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;

trait HasReports
{
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function report(array $data, Model $reporter): Report
    {
        $report = (new Report())->fill(Arr::except($data, ['reporter_id', 'reporter_type']));
        $report->reporter()->associate($reporter);

        $this->reports()->save($report);

        return $report;
    }

    public function isReportedBy(Model $reporter): bool
    {
        return $this->reports()->whereMorphedTo('reporter', $reporter)->exists();
    }
}
