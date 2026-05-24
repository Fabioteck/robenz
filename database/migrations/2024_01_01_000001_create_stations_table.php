<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mimit_id')->unique(); // idimpianto dal CSV MIMIT
            $table->string('manager');                         // gestore (Eni, IP, Q8…)
            $table->string('flag')->nullable();                // bandiera commerciale
            $table->string('type');                            // Stradale, Autostradale…
            $table->string('name')->nullable();                // nome impianto (opzionale)
            $table->string('address');
            $table->string('municipality');                    // comune
            $table->string('province', 2);                    // sigla provincia (es. RO)
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();

            $table->index('province');
            $table->index('municipality');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
