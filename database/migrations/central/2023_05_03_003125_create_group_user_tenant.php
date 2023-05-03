<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 May 2023 09:58:13 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('group_user_tenant', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_user_id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('user_id')->index()->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['group_user_id', 'tenant_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_user_tenant');
    }
};
