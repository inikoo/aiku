<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:18:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasOrderFields;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasOrderFields;


    public function up(): void
    {
        Schema::create('purchase_order_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table=$this->groupOrgRelationship($table);
            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->unsignedInteger('historic_supplier_product_id')->index();
            $table->foreign('historic_supplier_product_id')->references('id')->on('historic_supplier_products');
            $table->string('state')->index()->default(PurchaseOrderTransactionStateEnum::CREATING->value);
            $table->string('status')->index()->default(PurchaseOrderTransactionStatusEnum::PROCESSING->value);
            $table->decimal('unit_quantity');
            $table->decimal('unit_cost');
            $table->decimal('net_amount', 16)->default(0);
            $table->decimal('grp_net_amount', 16)->nullable();
            $table->decimal('org_net_amount', 16)->nullable();
            $table->decimal('grp_exchange', 16, 4)->nullable();
            $table->decimal('org_exchange', 16, 4)->nullable();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->datetimeTz('fetched_at')->nullable();
            $table->datetimeTz('last_fetched_at')->nullable();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purchase_order_transactions');
    }
};
