<?php

use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionDeliveryStatusEnum;
use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasProcurementStats;
use App\Stubs\Migrations\IsProcurementOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use IsProcurementOrder;
    use HasProcurementStats;
    public function up(): void
    {
        Schema::create('agent_supplier_purchase_orders', function (Blueprint $table) {
            
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->string('reference')->index();
            $table->string('slug')->unique()->collation('und_ns');

            $table->unsignedSmallInteger('purchase_order_id')->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedSmallInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->string('state')->index()->default(PurchaseOrderTransactionStateEnum::IN_PROCESS->value);
            $table->string('delivery_status')->index()->default(PurchaseOrderTransactionDeliveryStatusEnum::PROCESSING->value);
            $table->text('notes')->nullable();
            $table->dateTimeTz('date')->comment('latest relevant date');
            $table->dateTimeTz('in_process_at')->nullable();
            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('confirmed_at')->nullable();
            $table->dateTimeTz('settled_at')->nullable();
            $table->dateTimeTz('cancelled_at')->nullable();

            $table = $this->costingProcurementOrder($table);
            $table = $this->stockDeliveriesStats($table);
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
        Schema::dropIfExists('agent_supplier_purchase_orders');
    }
};
