<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('purchase_order_deposit_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->date('date');
            $table->decimal('amount', 16);
            $table->unsignedSmallInteger('currency_id')->index();
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->unsignedInteger('media_id')->nullable();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->unsignedSmallInteger('created_by')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement("
            insert into purchase_order_deposit_payments
                (group_id, organisation_id, purchase_order_id, date, amount, currency_id, method, reference, media_id, created_by, created_at, updated_at)
            select
                purchase_orders.group_id,
                purchase_orders.organisation_id,
                purchase_orders.id,
                purchase_orders.deposit_paid_at::date,
                purchase_orders.deposit_amount,
                purchase_orders.currency_id,
                coalesce(purchase_orders.data #>> '{payments,deposit,method}', 'bank_transfer'),
                purchase_orders.data #>> '{payments,deposit,reference}',
                (
                    select model_has_attachments.media_id from model_has_attachments
                    where model_has_attachments.model_type = 'PurchaseOrder'
                        and model_has_attachments.model_id = purchase_orders.id
                        and model_has_attachments.scope = 'Bank statement'
                        and model_has_attachments.sub_scope = 'deposit'
                    order by model_has_attachments.id desc
                    limit 1
                ),
                purchase_orders.deposit_paid_by_id,
                now(),
                now()
            from purchase_orders
            where purchase_orders.deposit_paid_at is not null
                and purchase_orders.deposit_amount > 0
                and purchase_orders.deleted_at is null
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_deposit_payments');
    }
};
