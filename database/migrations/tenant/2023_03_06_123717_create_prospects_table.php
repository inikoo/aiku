<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:46:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Leads\Prospect\ProspectStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('slug')->unique();
            $table->string('name', 256)->nullable()->fulltext();
            $table->string('contact_name', 256)->nullable()->index()->fulltext();
            $table->string('company_name', 256)->nullable();
            $table->string('email')->nullable()->fulltext();
            $table->string('phone')->nullable();
            $table->string('website', 256)->nullable();
            $table->jsonb('location');
            $table->string('state')->index()->default(ProspectStateEnum::NO_CONTACTED->value);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
