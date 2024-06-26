<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Apr 2024 17:12:30 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('recurring_bills', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('reference')->unique()->index();
            $table->unsignedSmallInteger('rental_agreement_id');
            $table->foreign('rental_agreement_id')->references('id')->on('rental_agreements');
            $table->unsignedSmallInteger('fulfilment_customer_id');
            $table->foreign('fulfilment_customer_id')->references('id')->on('fulfilment_customers');
            $table->unsignedSmallInteger('fulfilment_id');
            $table->foreign('fulfilment_id')->references('id')->on('fulfilments');
            $table->string('status')->nullable()->index()->default(RecurringBillStatusEnum::CURRENT->value);
            $table->dateTimeTz('start_date');
            $table->dateTimeTz('end_date')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->jsonb('data')->nullable();
            $table->timestampsTz();
            $table->softDeletes();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('recurring_bills');
    }
};
