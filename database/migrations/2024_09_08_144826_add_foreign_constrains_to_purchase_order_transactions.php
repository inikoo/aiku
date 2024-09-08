<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 08 Sept 2024 22:48:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->foreign('org_stock_id')->references('id')->on('org_stocks');
        });
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->foreign('org_supplier_product_id')->references('id')->on('org_supplier_products');
        });
    }


    public function down(): void
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->dropForeign('org_stocks_id_foreign');
        });
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->dropForeign('org_supplier_product_id_foreign');
        });
    }
};
