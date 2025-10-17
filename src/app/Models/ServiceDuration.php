<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDuration extends Model
{
    protected $fillable = ['service_id','minutes'];

    protected $casts = [
        'minutes' => 'integer',
    ];

    public function service() { return $this->belongsTo(Service::class); }
}
