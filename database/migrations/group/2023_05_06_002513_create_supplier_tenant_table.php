<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:55 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\SupplierTenant\SupplierTenantStatusEnum;
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
            $table->unsignedSmallInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->string('status')->default(SupplierTenantStatusEnum::ADOPTED->value);
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
