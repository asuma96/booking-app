<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'service_id','service_duration_id','start_at_utc','end_at_utc',
        'customer_name','customer_phone','status'
    ];

    protected $casts = [
        'start_at_utc' => 'datetime:UTC',
        'end_at_utc'   => 'datetime:UTC',
    ];

    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function duration(): BelongsTo { return $this->belongsTo(ServiceDuration::class,'service_duration_id'); }
}
