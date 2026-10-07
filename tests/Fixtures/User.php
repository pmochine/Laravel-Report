<?php

namespace Pmochine\Tests\Report\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Pmochine\Report\Traits\HasReports;
use Pmochine\Report\Traits\SubmitsReports;

class User extends Model
{
    use HasReports;
    use SubmitsReports;

    public $timestamps = false;

    protected $guarded = [];
}
