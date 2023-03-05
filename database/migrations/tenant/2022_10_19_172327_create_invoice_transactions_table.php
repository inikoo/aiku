<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 18:24:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('invoice_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedBigInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->foreignId('invoice_id')->constrained();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('transaction_id')->nullable()->constrained();


            $table->nullableMorphs('item');


            $table->decimal('quantity', 16, 3);
            $table->decimal('net', 16)->default(0);
            $table->decimal('discounts', 16)->default(0);

            $table->decimal('tax', 16)->default(0);
            $table->unsignedMediumInteger('tax_band_id')->nullable()->index();
            $table->jsonb('data');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->unsignedBigInteger('source_alt_id')->nullable()->index();
        });
    }


    public function down()
    {
        Schema::dropIfExists('invoice_transactions');
    }
};
