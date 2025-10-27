<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\BookingStatus;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->foreignId('service_duration_id')->constrained()->cascadeOnDelete();
            $t->timestamp('start_at_utc');
            $t->timestamp('end_at_utc');
            $t->string('customer_name');
            $t->string('customer_phone', 32);
            $t->enum('status', BookingStatus::values())->default(BookingStatus::BOOKED->value);
            $t->timestamps();

            $t->index(['service_id','start_at_utc','end_at_utc','status'], 'bookings_overlap_idx');
        });
    }

    public function down(): void { 
        Schema::dropIfExists('bookings'); 
    }
};
