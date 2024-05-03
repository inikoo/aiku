<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:17:59 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('supplier_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table=$this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('parent_type')->comment('OrgAgent|OrgSupplier|Organisation(intra-group sales)')->index();
            $table->unsignedInteger('parent_id')->index();
            $table->string('number');
            $table->string('state')->index()->default(SupplierDeliveryStateEnum::CREATING->value);
            $table->string('status')->index()->default(SupplierDeliveryStatusEnum::PROCESSING->value);
            $table->dateTimeTz('date')->comment('latest relevant date');

            $table->dateTimeTz('creating_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();

            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('checked_at')->nullable();
            $table->dateTimeTz('settled_at')->nullable();
            $table->dateTimeTz('cancelled_at')->nullable();


            $table->smallInteger('number_of_items')->default(0);
            $table->float('gross_weight', 16)->default(null)->nullable();
            $table->float('net_weight', 16)->default(null)->nullable();
            $table->decimal('cost_items', 16)->default(null)->nullable();
            $table->decimal('cost_extra', 16)->default(null)->nullable();
            $table->decimal('cost_shipping', 16)->default(null)->nullable();
            $table->decimal('cost_duties', 16)->default(null)->nullable();
            $table->decimal('cost_tax', 16)->default(0);
            $table->decimal('cost_total', 16)->default(0);

            $table->jsonb('data');

            $table->unsignedSmallInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->unsignedSmallInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedSmallInteger('partner_id')->nullable();
            $table->foreign('partner_id')->references('id')->on('organisations');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
            $table->index(['parent_id', 'parent_type']);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('supplier_deliveries');
    }
};
