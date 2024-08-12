<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:18:44 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStateEnum;
use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table=$this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('parent_type')->comment('OrgAgent|OrgSupplier|Organisation(intra-group sales)')->index();
            $table->unsignedInteger('parent_id')->index();
            $table->string('parent_code')->index()->collation('und_ns')->comment('Parent code on the time of consolidation');
            $table->string('parent_name')->index()->comment('Parent name on the time of consolidation');
            $table->string('number');
            $table->jsonb('data');
            $table->string('state')->index()->default(PurchaseOrderItemStateEnum::CREATING->value);
            $table->string('status')->index()->default(PurchaseOrderItemStatusEnum::PROCESSING->value);
            $table->dateTimeTz('date')->comment('latest relevant date');
            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('confirmed_at')->nullable();
            $table->dateTimeTz('manufactured_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();
            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('checked_at')->nullable();
            $table->dateTimeTz('settled_at')->nullable();
            $table->dateTimeTz('cancelled_at')->nullable();
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->decimal('grp_exchange', 16, 4)->nullable();
            $table->decimal('org_exchange', 16, 4)->nullable();
            $table->smallInteger('number_of_items')->default(0);
            $table->float('gross_weight', 16)->default(null)->nullable();
            $table->float('net_weight', 16)->default(null)->nullable();
            $table->decimal('cost_items', 16)->default(null)->nullable();
            $table->decimal('cost_extra', 16)->default(null)->nullable();
            $table->decimal('cost_shipping', 16)->default(null)->nullable();
            $table->decimal('cost_duties', 16)->default(null)->nullable();
            $table->decimal('cost_tax', 16)->default(0);
            $table->decimal('cost_total', 16)->default(0);

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
        Schema::dropIfExists('purchase_orders');
    }
};
