<?php

use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderStateEnum;
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
        Schema::table('payment_service_providers', function (Blueprint $table) {
            $table->string('state')->default(PaymentServiceProviderStateEnum::ACTIVE->value)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_service_providers', function (Blueprint $table) {
            //
        });
    }
};
