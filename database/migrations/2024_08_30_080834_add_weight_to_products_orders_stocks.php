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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight')->nullable()->after('description')->comment('in grams');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('estimated_weight')->nullable()->after('currency_id')->comment('in grams');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedInteger('weight')->nullable()->after('sellable')->comment('in grams');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('estimated_weight');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
