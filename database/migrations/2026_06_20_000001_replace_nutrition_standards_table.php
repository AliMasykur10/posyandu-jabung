<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('nutrition_standards');

        Schema::create('nutrition_standards', function (Blueprint $table) {
            $table->id();
            $table->string('indicator', 8);
            $table->enum('gender', ['L', 'P']);
            $table->decimal('reference_value', 7, 1);
            $table->decimal('l', 14, 8);
            $table->decimal('m', 14, 8);
            $table->decimal('s', 14, 8);
            $table->string('source_version');
            $table->timestamps();

            $table->unique(['indicator', 'gender', 'reference_value'], 'nutrition_standard_reference_unique');
            $table->index(['indicator', 'gender']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_standards');

        Schema::create('nutrition_standards', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['L', 'P']);
            $table->integer('age_month');
            $table->decimal('min_3sd', 5, 2);
            $table->decimal('min_2sd', 5, 2);
            $table->decimal('median', 5, 2);
            $table->decimal('plus_1sd', 5, 2);
            $table->timestamps();
        });
    }
};
