<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:00:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create(
            'invoice_order',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('order_id');
                $table->foreign('order_id')->references('id')->on('orders');
                $table->unsignedInteger('invoice_id');
                $table->foreign('invoice_id')->references('id')->on('invoices');

                $table->timestampsTz();
                $table->unique(
                    [
                        'order_id',
                        'invoice_id'
                    ]
                );
            }
        );
    }


    public function down()
    {
        Schema::dropIfExists('invoice_order');
    }
};
