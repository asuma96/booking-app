<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    protected $fillable = ['service_id','weekday','start_time','end_time'];
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
