<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:29:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->unsignedMediumInteger('shop_id')->index()->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->string('name', 256)->nullable()->fulltext();
            $table->string('contact_name', 256)->nullable()->index()->fulltext();
            $table->string('company_name', 256)->nullable();
            $table->string('email')->nullable()->fulltext();
            $table->string('phone')->nullable();
            $table->string('identity_document_number')->nullable();
            $table->string('website', 256)->nullable();
            $table->string('tax_number')->nullable()->index();
            $table->enum('tax_number_status', ['valid', 'invalid', 'na', 'unknown'])->nullable()->default('na');
            $table->jsonb('tax_number_data');
            $table->jsonb('location');

            $table->enum('status', ['pending-approval', 'approved', 'rejected', 'banned'])->index();
            $table->enum('state', ['in-process', 'active', 'losing', 'lost', 'registered'])->index()->nullable();
            $table->enum('trade_state', ['none', 'one', 'many'])->index()->nullable()->default('none')->comment('number of invoices');

            $table->unsignedBigInteger('billing_address_id')->nullable()->index();
            $table->foreign('billing_address_id')->references('id')->on('addresses');
            $table->unsignedBigInteger('delivery_address_id')->nullable()->index()->comment('null for customers in fulfilment shops');
            $table->foreign('delivery_address_id')->references('id')->on('addresses');


            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unsignedBigInteger('source_id')->nullable()->unique()->index();

            $table->index([DB::raw('name(64)')]);
        });


    }


    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
