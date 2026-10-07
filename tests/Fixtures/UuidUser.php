<?php

namespace Pmochine\Tests\Report\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Pmochine\Report\Traits\SubmitsReports;

class UuidUser extends Model
{
    use HasUuids;
    use SubmitsReports;

    public $timestamps = false;

    protected $guarded = [];
}
