<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Stubs\Migrations\HasFulfilmentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasFulfilmentStats;

    public function up(): void
    {
        Schema::create('tenant_fulfilment_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');
            $table = $this->containerFulfilmentStats($table);
            $table = $this->fulfilmentStats($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenant_fulfilment_stats');
    }
};
