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
            $table->unsignedInteger('provider_id')->index();
            $table->string('provider_type');
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
            $table->decimal('group_exchange', 16, 4)->default(1);
            $table->decimal('org_exchange', 16, 4)->default(1);
            $table->smallInteger('number_of_items')->default(0);
            $table->float('gross_weight', 16)->default(null)->nullable();
            $table->float('net_weight', 16)->default(null)->nullable();
            $table->decimal('cost_items', 16)->default(null)->nullable();
            $table->decimal('cost_extra', 16)->default(null)->nullable();
            $table->decimal('cost_shipping', 16)->default(null)->nullable();
            $table->decimal('cost_duties', 16)->default(null)->nullable();
            $table->decimal('cost_tax', 16)->default(0);
            $table->decimal('cost_total', 16)->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();


            $table->index(['provider_id', 'provider_type']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
