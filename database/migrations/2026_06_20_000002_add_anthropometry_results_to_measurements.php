<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->string('measurement_method', 16)->nullable()->after('height');
            $table->unsignedSmallInteger('age_days')->nullable()->after('measurement_method');
            $table->decimal('standardized_height', 5, 2)->nullable()->after('age_days');
            $table->decimal('bmi', 6, 2)->nullable()->after('standardized_height');

            $table->decimal('bb_u_zscore', 6, 2)->nullable()->after('bmi');
            $table->string('bb_u_status')->nullable()->after('bb_u_zscore')->index();
            $table->boolean('bb_u_flagged')->default(false)->after('bb_u_status');

            $table->decimal('tb_u_zscore', 6, 2)->nullable()->after('bb_u_flagged');
            $table->string('tb_u_status')->nullable()->after('tb_u_zscore')->index();
            $table->boolean('tb_u_flagged')->default(false)->after('tb_u_status');

            $table->decimal('bb_tb_zscore', 6, 2)->nullable()->after('tb_u_flagged');
            $table->string('bb_tb_status')->nullable()->after('bb_tb_zscore')->index();
            $table->boolean('bb_tb_flagged')->default(false)->after('bb_tb_status');

            $table->decimal('imt_u_zscore', 6, 2)->nullable()->after('bb_tb_flagged');
            $table->string('imt_u_status')->nullable()->after('imt_u_zscore')->index();
            $table->boolean('imt_u_flagged')->default(false)->after('imt_u_status');
            $table->string('calculation_version')->nullable()->after('imt_u_flagged');
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropIndex(['bb_u_status']);
            $table->dropIndex(['tb_u_status']);
            $table->dropIndex(['bb_tb_status']);
            $table->dropIndex(['imt_u_status']);
            $table->dropColumn([
                'measurement_method',
                'age_days',
                'standardized_height',
                'bmi',
                'bb_u_zscore',
                'bb_u_status',
                'bb_u_flagged',
                'tb_u_zscore',
                'tb_u_status',
                'tb_u_flagged',
                'bb_tb_zscore',
                'bb_tb_status',
                'bb_tb_flagged',
                'imt_u_zscore',
                'imt_u_status',
                'imt_u_flagged',
                'calculation_version',
            ]);
        });
    }
};
