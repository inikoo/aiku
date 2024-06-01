<?php

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('rental_agreement_clauses', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('fulfilment_id');
            $table->foreign('fulfilment_id')->references('id')->on('fulfilments');
            $table->unsignedInteger('fulfilment_customer_id');
            $table->foreign('fulfilment_customer_id')->references('id')->on('fulfilment_customers');
            $table->unsignedInteger('billable_id');
            $table->foreign('billable_id')->references('id')->on('billables');

            $table->unsignedInteger('rental_agreement_id');
            $table->foreign('rental_agreement_id')->references('id')->on('rental_agreements');

            $table->decimal('agreed_price');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('rental_agreement_clauses');
    }
};
