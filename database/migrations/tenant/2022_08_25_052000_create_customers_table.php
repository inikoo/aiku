<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:29:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Enums\Sales\Customer\CustomerTradeStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->index()->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');
            $table->string('slug')->unique();
            $table->string('reference')->unique()->comment('customer public id');
            $table->string('name', 256)->nullable()->fulltext();
            $table->string('contact_name', 256)->nullable()->index()->fulltext();
            $table->string('company_name', 256)->nullable();
            $table->string('email')->nullable()->fulltext();
            $table->string('phone')->nullable();
            $table->string('identity_document_number')->nullable();
            $table->string('website', 256)->nullable();
            $table->jsonb('location');
            $table->string('status')->index();
            $table->string('state')->index()->default(CustomerStateEnum::IN_PROCESS->value);
            $table->string('trade_state')->index()->default(CustomerTradeStateEnum::NONE->value)->comment('number of invoices');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
