<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 13:13:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('number')->index();
            $table->enum('type', ['order', 'replacement'])->default('order')->index();

            $table->enum(
                'state',
                [
                    'submitted',
                    'in-queue',
                    'picker-assigned',
                    'picking',
                    'picked',
                    'packing',
                    'packed',
                    'finalised',
                    'dispatched',
                    'cancelled'
                ]
            )->index();

            $table->boolean('can_dispatch')->nullable();
            $table->boolean('restocking')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();


            $table->unsignedBigInteger('shipment_id')->nullable()->index();
            $table->foreign('shipment_id')->references('id')->on('shipments');

            $table->decimal('weight', 16)->nullable()->default(0);
            $table->unsignedMediumInteger('number_stocks')->default(0);
            $table->unsignedMediumInteger('number_picks')->default(0);


            $table->unsignedMediumInteger('picker_id')->nullable()->index()->comment('Main picker');
            $table->foreign('picker_id')->references('id')->on('employees');
            $table->unsignedMediumInteger('packer_id')->nullable()->index()->comment('Main packer');
            $table->foreign('packer_id')->references('id')->on('employees');

            $table->dateTimeTz('date')->index();

            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('assigned_at')->nullable();
            $table->dateTimeTz('picking_at')->nullable();
            $table->dateTimeTz('picked_at')->nullable();

            $table->dateTimeTz('packing_at')->nullable();
            $table->dateTimeTz('packed_at')->nullable();
            $table->dateTimeTz('finalised_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();

            $table->dateTimeTz('cancelled_at')->nullable();


            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('delivery_notes');
    }
};
