<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 14:18:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_account_id')->constrained();
            $table->unsignedMediumInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->foreignId('customer_id')->constrained();




            $table->string('reference')->index();
            $table->string('slug')->unique();
            $table->enum('status', ['in-process','success','fail'])->index();

            //PaymentStateEnum
            $table->string('state')->index();
            $table->enum('subsequent_status', ['unchanged','refunded','with-refund'])->index()->nullable();

            $table->decimal('amount', 12, 2);
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('central.currencies');
            $table->decimal('dc_amount', 12, 2);

            $table->jsonb('data');
            $table->dateTimeTz('date')->index()->comment('Most relevant date at current state');
            $table->dateTimeTz('completed_at')->nullable();
            $table->dateTimeTz('cancelled_at')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->enum('type', ['payment','refund']);
            $table->boolean('with_refund')->default(false);
            $table->unsignedBigInteger('source_id')->index()->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
