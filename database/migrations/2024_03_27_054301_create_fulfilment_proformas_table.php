<?php

use App\Enums\Fulfilment\Proforma\ProformaTypeEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSalesTransactionParents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesTransactionParents;
    use HasGroupOrganisationRelationship;

    public function up()
    {
        Schema::create('fulfilment_proformas', function (Blueprint $table) {
            $table->increments('id');
            $table=$this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('number')->index();
            $table=$this->salesTransactionParents($table);
            $table->string('type')->default(ProformaTypeEnum::INVOICE->value)->index();
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->decimal('group_exchange', 16, 4)->default(1);
            $table->decimal('org_exchange', 16, 4)->default(1);
            $table->decimal('net', 16)->default(0);
            $table->decimal('total', 16)->default(0);
            $table->decimal('payment', 16)->default(0);
            $table->decimal('group_net_amount', 16)->default(0);
            $table->decimal('org_net_amount', 16)->default(0);
            $table->dateTimeTz('paid_at')->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->index()->nullable();
        });
    }


    public function down()
    {
        Schema::dropIfExists('fulfilment_proformas');
    }
};
