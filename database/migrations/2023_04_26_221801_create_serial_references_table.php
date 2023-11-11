<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 21:55:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('serial_references', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->morphs('container');
            $table->unsignedSmallInteger('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->string('model')->index();
            $table->unsignedBigInteger('serial')->default(0);
            $table->string('format')->default("%06d");
            $table->jsonb('data');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('serial_references');
    }
};
