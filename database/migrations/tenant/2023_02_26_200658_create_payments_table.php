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
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('payment_account_id')->index();
            $table->foreign('payment_account_id')->references('id')->on('payment_accounts');

            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');



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
            $table->unsignedInteger('source_id')->index()->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
