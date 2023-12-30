<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\SupplierOrganisation\SupplierOrganisationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_organisation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedSmallInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->string('status')->default(SupplierOrganisationStatusEnum::ADOPTED->value);
            $table->timestampsTz();
            $table->string('source_id')->index()->nullable();
            $table->index(['organisation_id','source_id']);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('supplier_organisation');
    }
};
