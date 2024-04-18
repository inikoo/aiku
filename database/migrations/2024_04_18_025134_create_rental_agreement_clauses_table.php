<?php

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up()
    {
        Schema::create('rental_agreement_clauses', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedInteger('fulfilment_customer_id');
            $table->foreign('fulfilment_customer_id')->references('id')->on('fulfilment_customers');

            $table->unsignedInteger('rental_id');
            $table->foreign('rental_id')->references('id')->on('rentals');

            $table->decimal('agreed_price');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('rental_agreement_clauses');
    }
};
