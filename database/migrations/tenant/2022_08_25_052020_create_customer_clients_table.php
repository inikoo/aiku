<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 23:58:31 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('customer_clients', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('reference')->nullable()->index();
            $table->boolean('status')->default(true)->index();
            $table->unsignedMediumInteger('shop_id')->index()->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedMediumInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('name')->nullable();
            $table->string('contact_name')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->jsonb('location');
            $table->unsignedBigInteger('delivery_address_id')->nullable()->index();
            $table->foreign('delivery_address_id')->references('id')->on('addresses');
            $table->dateTimeTz('deactivated_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();

            $table->index([DB::raw('name(64)')]);
        });
    }


    public function down()
    {
        Schema::dropIfExists('customer_clients');
    }
};
