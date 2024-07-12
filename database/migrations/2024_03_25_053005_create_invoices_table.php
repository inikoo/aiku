<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 07:21:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasOrderAmountTotals;
use App\Stubs\Migrations\HasSalesTransactionParents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesTransactionParents;
    use HasGroupOrganisationRelationship;
    use HasOrderAmountTotals;

    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('number')->index();
            $table = $this->salesTransactionParents($table);
            $table->unsignedInteger('address_id')->nullable()->index();
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->unsignedSmallInteger('billing_country_id')->index()->nullable();
            $table->foreign('billing_country_id')->references('id')->on('countries');
            $table->string('type')->default(InvoiceTypeEnum::INVOICE)->index();

            $table=$this->currencyFields($table);
            $table=$this->orderTotalAmounts($table);

            $table->dateTimeTz('date')->index()->nullable();
            $table->dateTimeTz('tax_liability_at')->nullable();
            $table->dateTimeTz('paid_at')->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->index()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
