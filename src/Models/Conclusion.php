<?php

/*
 * This file is part of Laravel Reportable.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pmochine\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Pmochine\Report\Events\ReportConcluded;

class Conclusion extends Model
{
    protected $table = 'reports_conclusions';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dispatchesEvents = ['created' => ReportConcluded::class];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function judge(): MorphTo
    {
        return $this->morphTo();
    }
}
