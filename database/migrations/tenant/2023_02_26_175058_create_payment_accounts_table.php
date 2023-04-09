<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 09:51:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->smallIncrements('id');

            $table->unsignedInteger('payment_service_provider_id')->index();
            $table->foreign('payment_service_provider_id')->references('id')->on('payment_service_providers');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');
            $table->string('code')->index();
            $table->string('slug')->unique();
            $table->string('name')->index();
            $table->jsonb('data');
            $table->dateTimeTz('last_used_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->index()->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('payment_accounts');
    }
};
