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
        Schema::create('nutrition_standards', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['L', 'P']);
            $table->integer('age_month');
            $table->decimal('min_3sd', 5, 2); // Sangat Kurang
            $table->decimal('min_2sd', 5, 2); // Kurang
            $table->decimal('median', 5, 2);  // Normal
            $table->decimal('plus_1sd', 5, 2); // Risiko Berat Lebih
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_standards');
    }
};
