<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 10 Nov 2024 13:44:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasProcurementOrderFields
{
    public function procurementItemFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('supplier_product_id')->nullable()->index();
        $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
        $table->unsignedInteger('historic_supplier_product_id')->nullable()->index();
        $table->foreign('historic_supplier_product_id')->references('id')->on('historic_supplier_products');
        $table->unsignedInteger('org_supplier_product_id')->nullable()->index();
        $table->foreign('org_supplier_product_id')->references('id')->on('org_supplier_products');
        $table->unsignedInteger('stock_id')->nullable()->index()->comment('Null allowed when org_stock is exclusive to an organization');
        $table->foreign('stock_id')->references('id')->on('stocks');
        $table->unsignedInteger('org_stock_id')->index();
        $table->foreign('org_stock_id')->references('id')->on('org_stocks');

        return $table;
    }
}
