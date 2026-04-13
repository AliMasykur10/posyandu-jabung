<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel children yang sudah kita ubah tadi
            $table->foreignId('child_id')->constrained()->onDelete('cascade');
            
            $table->date('measurement_date'); // tgl_timbang -> measurement_date
            $table->decimal('weight', 5, 2);  // berat_badan -> weight (kg)
            $table->decimal('height', 5, 2);  // tinggi_badan -> height (cm)
            
            // status_gizi -> nutritional_status
            $table->string('nutritional_status')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};