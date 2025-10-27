<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayLock extends Model
{
    public $timestamps = false;
    protected $fillable = ['service_id','date'];
    protected $table = 'day_locks';
}
