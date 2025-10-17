<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['venue_id','name'];
    public function venue(): BelongsTo { return $this->belongsTo(Venue::class); }
    public function durations(): HasMany { return $this->hasMany(ServiceDuration::class); }
    public function schedules(): HasMany { return $this->hasMany(ServiceSchedule::class); }
}
