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

            // Menghubungkan ke tabel children
            $table->foreignId('child_id')->constrained()->onDelete('cascade');

            $table->decimal('weight', 5, 2); // Berat badan (kg)
            $table->decimal('height', 5, 2); // Tinggi/Panjang badan (cm)
            $table->date('measurement_date'); // Tanggal penimbangan
            $table->text('notes')->nullable(); // Catatan tambahan (misal: imunisasi apa)

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
