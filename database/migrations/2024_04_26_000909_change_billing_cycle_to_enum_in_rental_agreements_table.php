<?php

use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_agreements', function (Blueprint $table) {
            $table->string('billing_cycle')->default(RentalAgreementBillingCycleEnum::WEEKLY->value)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rental_agreements', function (Blueprint $table) {
            //
        });
    }
};
