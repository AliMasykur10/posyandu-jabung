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
        Schema::create('children', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel posyandus (otomatis mencari tabel 'posyandus')
            $table->foreignId('posyandu_id')->constrained()->onDelete('cascade');

            // Relasi ke tabel parent_details (harus ditulis manual karena namanya custom)
            $table->foreignId('parent_id')->constrained('parent_details')->onDelete('cascade');

            $table->string('name'); // nama_anak -> name
            $table->date('birth_date'); // tgl_lahir -> birth_date
            $table->enum('gender', ['L', 'P']); // jk -> gender
            $table->decimal('birth_weight', 5, 2); // berat_lahir -> birth_weight

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
