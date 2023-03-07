<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 18:58:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('delivery_note_id')->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');

            $table->unsignedInteger('stock_id')->index();
            $table->foreign('stock_id')->references('id')->on('stocks');

            $table->unsignedInteger('transaction_id')->index();
            $table->foreign('transaction_id')->references('id')->on('transactions');

            $table->unsignedInteger('picking_id')->nullable()->index();
            $table->foreign('picking_id')->references('id')->on('pickings');

            $table->enum(
                'state',
                [
                    'on-hold',
                    'picking',
                    'picked',
                    'packed',
                    'dispatched',
                    'fail',
                    'cancelled'
                ]
            )->index();
            $table->enum('status', ['in-process','done','done-with-missing','fail']);

            $table->decimal('required', 16, 3);
            $table->decimal('quantity', 16, 3)->default(0);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
