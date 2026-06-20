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

            // ➕ TAMBAHKAN KOLOM DATA BARU DI SINI
            $table->decimal('head_circumference', 5, 2)->nullable(); // Lingkar Kepala (cm)
            $table->decimal('arm_circumference', 5, 2)->nullable();  // Lingkar Lengan / LiLA (cm)
            $table->string('vitamin_a')->nullable();                 // Status Vitamin A (Merah/Biru/Tidak)
            $table->boolean('deworming_medicine')->default(false);    // Obat Cacing (Ya/Tidak)
            $table->string('pmt_status')->nullable();                 // Pemberian Makanan Tambahan
            $table->string('status_gizi')->nullable();                // Hasil Kalkulasi Status Gizi

            $table->date('measurement_date'); // Tanggal penimbangan (Tetap pertahankan nama aslinya)
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
