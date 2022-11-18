<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 15:31:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('shippers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->string('api_shipper')->nullable()->index();
            $table->boolean('status')->default('true')->index();
            $table->string('name')->index();
            $table->string('contact_name', 256)->nullable();
            $table->string('company_name', 256)->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website', 256)->nullable();
            $table->string('tracking_url')->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });


    }

    public function down()
    {
        Schema::dropIfExists('shippers');
    }
};
