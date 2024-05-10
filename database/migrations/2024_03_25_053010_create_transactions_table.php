<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 27 Aug 2022 23:08:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\OMS\Transaction\TransactionStateEnum;
use App\Enums\OMS\Transaction\TransactionStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSalesTransactionParents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesTransactionParents;
    use HasGroupOrganisationRelationship;

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
            $table->string('state')->default(TransactionStateEnum::CREATING->value)->index();
            $table->string('status')->default(TransactionStatusEnum::PROCESSING->value)->index();

            $table->nullableMorphs('item');
            $table->decimal('quantity_ordered', 16, 3);
            $table->decimal('quantity_bonus', 16, 3);
            $table->decimal('quantity_dispatched', 16, 3)->default(0);
            $table->decimal('quantity_fail', 16, 3)->default(0);
            $table->decimal('quantity_cancelled', 16, 3)->default(0);


            $table->decimal('discounts', 16)->default(0);

            $table->decimal('net', 16)->default(0);
            $table->decimal('group_net_amount', 16)->default(0);
            $table->decimal('org_net_amount', 16)->default(0);


            $table->decimal('tax_rate', 16)->default(0);


            //$table->unsignedSmallInteger('tax_band_id')->nullable()->index();
            //$table->foreign('tax_band_id')->references('id')->on('tax_bands');


            $table->decimal('group_exchange', 16, 4)->default(1);
            $table->decimal('org_exchange', 16, 4)->default(1);
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
