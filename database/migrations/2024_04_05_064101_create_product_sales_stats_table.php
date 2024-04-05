<?php

use App\Stubs\Migrations\HasSalesStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesStats;

    public function up()
    {
        Schema::create('product_sales_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');

            $table=$this->salesStats($table, ['shop_amount','org_amount','group_amount']);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('product_sales_stats');
    }
};
