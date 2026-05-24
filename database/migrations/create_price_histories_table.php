<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->string('fuel_type', 50);
            $table->boolean('is_self');
            $table->decimal('price', 5, 3);
            $table->date('recorded_at');
            $table->timestamps();

            $table->index(['station_id', 'fuel_type', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
