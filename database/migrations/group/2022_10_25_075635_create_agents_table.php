<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 11:28:24 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->smallIncrements('id');

            $table->unsignedSmallInteger('owner_id')->comment('Tenant who owns this model');
            $table->foreign('owner_id')->references('id')->on('public.tenants');
            $table->boolean('status')->default(true)->index();
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->string('name');
            $table->string('company_name', 256)->nullable();
            $table->string('contact_name', 256)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_private')->default(true);
            $table->unsignedInteger('address_id')->nullable()->index();
            $table->foreign('address_id')->references('id')->on('group_addresses');
            $table->jsonb('location');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('group_media');
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('public.currencies');
            $table->jsonb('settings');
            $table->jsonb('shared_data');
            $table->jsonb('tenant_data');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->string('source_type')->index()->nullable();
            $table->unsignedInteger('source_id')->index()->nullable();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
