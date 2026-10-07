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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Pmochine\Report\Events\ReportCreated;

class Report extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dispatchesEvents = ['created' => ReportCreated::class];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): MorphTo
    {
        return $this->morphTo();
    }

    public function conclusion(): HasOne
    {
        return $this->hasOne(Conclusion::class)->latestOfMany();
    }

    public function conclusions(): HasMany
    {
        return $this->hasMany(Conclusion::class);
    }

    /**
     * Reports without a conclusion.
     */
    public function scopePending(Builder $query): void
    {
        $query->doesntHave('conclusions');
    }

    /**
     * Reports with at least one conclusion.
     */
    public function scopeConcluded(Builder $query): void
    {
        $query->has('conclusions');
    }

    public function judge(): ?Model
    {
        return $this->conclusion?->judge;
    }

    public function conclude(array $data, Model $judge): Conclusion
    {
        $conclusion = (new Conclusion())->fill(Arr::except($data, ['judge_id', 'judge_type']));
        $conclusion->judge()->associate($judge);

        if ($this->conclusion()->save($conclusion)) {
            $this->setRelation('conclusion', $conclusion);
        }

        return $conclusion;
    }

    public static function allJudges(): array
    {
        return Conclusion::with('judge')->get()
            ->pluck('judge')
            ->filter()
            ->unique(fn (Model $judge) => $judge->getMorphClass() . ':' . $judge->getKey())
            ->values()
            ->all();
    }
}
