<?php

namespace Pmochine\Tests\Report\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Pmochine\Report\Traits\HasReports;

class UuidPost extends Model
{
    use HasReports;
    use HasUuids;

    public $timestamps = false;

    protected $guarded = [];
}
