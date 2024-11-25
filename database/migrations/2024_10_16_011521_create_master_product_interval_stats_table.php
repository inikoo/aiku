<?php

use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;
    public function up(): void
    {
        Schema::create('master_product_sales_intervals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('master_product_id')->index();
            $table->foreign('master_product_id')->references('id')->on('master_products');
            $table = $this->salesIntervalFields($table, [
                'sales_grp_currency',
                'invoices',
                'orders',
                'delivery_notes',
                'customers'
            ]);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_product_sales_intervals');
    }
};
