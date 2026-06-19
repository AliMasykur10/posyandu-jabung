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
        Schema::create('parent_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // 🔑 MULTI-TENANT: Mengunci data keluarga agar terikat ke salah satu posyandu
            $table->foreignId('posyandu_id')->constrained('posyandus')->onDelete('cascade');

            // 🔒 VALIDASI IDENTITAS UNIK (Wajib String untuk mencegah angka 0 hilang atau integer overflow)
            $table->string('no_kk', 16)->unique();
            $table->string('nik_mother', 16)->nullable(); // Menyesuaikan kebutuhan e-PPGBM Kemenkes
            $table->string('nik_father', 16)->nullable();

            // DATA UTAMA (Bawaan aslimu)
            $table->string('mother_name');
            $table->string('father_name')->nullable();
            $table->string('phone_number')->nullable();

            // ELEMEN DOMISILI KEMENKES
            $table->text('address');
            $table->string('rt', 3);
            $table->string('rw', 3);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_details');
    }
};
