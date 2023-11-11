<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:55 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\AgentTenant\AgentTenantStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_tenant', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('agent_id');
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants');
            $table->string('status')->default(AgentTenantStatusEnum::ADOPTED->value);
            $table->timestampsTz();
            $table->unsignedInteger('source_id')->index()->nullable();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('agent_tenant');
    }
};
