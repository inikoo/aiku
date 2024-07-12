<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 23:08:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasOrderFields;
use App\Stubs\Migrations\HasSalesTransactionParents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesTransactionParents;
    use HasGroupOrganisationRelationship;
    use HasOrderFields;

    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table = $this->salesTransactionParents($table);
            $table->unsignedInteger('invoice_id')->nullable()->index();
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->string('type');
            $table->datetimeTz('date');
            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('in_warehouse_at')->nullable();

            $table->dateTimeTz('settled_at')->nullable();

            $table->string('state')->default(TransactionStateEnum::CREATING->value)->index();
            $table->string('status')->default(TransactionStatusEnum::PROCESSING->value)->index();

            $table->string('asset_type')->index();
            $table->unsignedInteger('asset_id')->index();
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->unsignedInteger('historic_asset_id')->index();
            $table->foreign('historic_asset_id')->references('id')->on('historic_assets');

            $table->decimal('quantity_ordered', 16, 3);
            $table->decimal('quantity_bonus', 16, 3)->default(0);
            $table->decimal('quantity_dispatched', 16, 3)->default(0);
            $table->decimal('quantity_fail', 16, 3)->default(0);
            $table->decimal('quantity_cancelled', 16, 3)->default(0);


            $table= $this->orderMoneyFields($table);

            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
