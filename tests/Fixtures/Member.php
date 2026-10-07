<?php

namespace Pmochine\Tests\Report\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'member_id';

    protected $guarded = [];
}
