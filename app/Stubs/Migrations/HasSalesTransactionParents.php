<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 May 2023 15:48:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasSalesTransactionParents
{
    public function salesTransactionParents(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('fulfilment_id')->index();
        $table->foreign('fulfilment_id')->references('id')->on('fulfilments');
        $table->unsignedInteger('fulfilment_customer_id')->index();
        $table->foreign('fulfilment_customer_id')->references('id')->on('fulfilment_customers');
        $table->unsignedInteger('order_id')->nullable()->index();
        $table->foreign('order_id')->references('id')->on('orders');

        return $table;
    }
}
