<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 19:01:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('pickings', function (Blueprint $table) {
            $table->increments('id');

            $table->boolean('fulfilled')->default(false)->index();

            $table->enum('state', ['created', 'assigned', 'picking', 'queried', 'waiting', 'picked', 'packing', 'done'])->index()->default('created');
            $table->enum('status', ['processing', 'packed', 'partially_packed', 'out_of_stock', 'cancelled'])->index()->default('processing');


            $table->unsignedInteger('delivery_note_id')->index();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');

            $table->unsignedInteger('org_stock_movement_id')->nullable()->index();
            $table->foreign('org_stock_movement_id')->references('id')->on('org_stock_movements');

            $table->unsignedInteger('stock_id')->index();
            $table->foreign('stock_id')->references('id')->on('stocks');

            $table->unsignedSmallInteger('picker_id')->nullable()->index();
            $table->foreign('picker_id')->references('id')->on('employees');

            $table->unsignedSmallInteger('packer_id')->nullable()->index();
            $table->foreign('packer_id')->references('id')->on('employees');

            $table->decimal('required', 16, 3);
            $table->decimal('picked', 16, 3)->nullable();

            $table->decimal('weight', 16, 3)->nullable();

            $table->jsonb('data');


            $table->dateTimeTz('assigned_at')->nullable();
            $table->dateTimeTz('picking_at')->nullable();
            $table->dateTimeTz('picked_at')->nullable();
            $table->dateTimeTz('packing_at')->nullable();
            $table->dateTimeTz('packed_at')->nullable();


            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pickings');
    }
};
