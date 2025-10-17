<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('day_locks', function (Blueprint $t) {
            $t->unsignedBigInteger('service_id');
            $t->date('date');
            $t->primary(['service_id','date']);
        });
    }
    public function down(): void { Schema::dropIfExists('day_locks'); }
};
