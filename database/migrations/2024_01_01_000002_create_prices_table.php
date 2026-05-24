<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->string('fuel_type');       // Benzina, Gasolio, GPL, Metano…
            $table->decimal('price', 6, 3);    // es. 1.789
            $table->boolean('is_self');         // true = self service, false = servito
            $table->timestamp('reported_at');   // dtComu dal CSV
            $table->date('reference_date');     // giorno del dato
            $table->timestamps();

            // Un impianto può avere un solo prezzo attivo per tipo+modalità
            $table->unique(['station_id', 'fuel_type', 'is_self'], 'prices_station_fuel_mode_unique');

            $table->index(['fuel_type', 'is_self', 'price']);
            $table->index('reference_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
