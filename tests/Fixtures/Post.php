<?php

namespace Pmochine\Tests\Report\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Pmochine\Report\Traits\HasReports;

class Post extends Model
{
    use HasReports;

    public $timestamps = false;

    protected $guarded = [];
}
