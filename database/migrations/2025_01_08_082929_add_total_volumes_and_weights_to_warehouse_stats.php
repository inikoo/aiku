<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_stats', function (Blueprint $table) {
            $table->decimal('total_volume')->default(0);
            $table->decimal('total_weight')->default(0);
        });
        Schema::table('warehouse_area_stats', function (Blueprint $table) {
            $table->decimal('total_volume')->default(0);
            $table->decimal('total_weight')->default(0);
        });
        Schema::table('warehouse_stats', function (Blueprint $table) {
            $table->decimal('total_volume')->default(0);
            $table->decimal('total_weight')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_stats', function (Blueprint $table) {
            $table->dropColumn(['total_volume', 'total_weight']);
        });
    
        Schema::table('warehouse_area_stats', function (Blueprint $table) {
            $table->dropColumn(['total_volume', 'total_weight']);
        });
    
        Schema::table('warehouse_stats', function (Blueprint $table) {
            $table->dropColumn(['total_volume', 'total_weight']);
        });
    }
};
