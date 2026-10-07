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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

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
        return $this->hasOne(Conclusion::class);
    }

    public function judge(): ?Model
    {
        return $this->conclusion?->judge;
    }

    public function conclude(array $data, Model $judge): Conclusion
    {
        $conclusion = (new Conclusion())->fill($data);
        $conclusion->judge()->associate($judge);

        $this->conclusion()->save($conclusion);
        $this->setRelation('conclusion', $conclusion);

        return $conclusion;
    }

    public static function allJudges(): array
    {
        $judges = [];

        foreach (Conclusion::get() as $conclusion) {
            $judges[] = $conclusion->judge;
        }

        return $judges;
    }
}
