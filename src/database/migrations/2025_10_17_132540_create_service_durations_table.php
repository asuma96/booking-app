<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_durations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('minutes');
            $t->timestamps();
            $t->unique(['service_id','minutes']);
        });
    }
    public function down(): void { Schema::dropIfExists('service_durations'); }
};
