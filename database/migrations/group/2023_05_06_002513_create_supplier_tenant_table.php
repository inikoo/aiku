<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:55 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_tenant', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants');
            $table->enum('type', ['supplier', 'sub-supplier'])->default('supplier')->index()->comment('sub-supplier: agents supplier');
            $table->boolean('status')->default(true);
            $table->boolean('is_owner')->default(false);
            $table->timestampsTz();
            $table->unsignedInteger('source_id')->index()->nullable();
            $table->index(['tenant_id','source_id']);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('supplier_tenant');
    }
};
