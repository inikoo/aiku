<?php

use App\Enums\Inventory\PalletDelivery\PalletDeliveryStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('pallet_deliveries', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();

            $table->unsignedSmallInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->string('name');
            $table->string('state')->default(PalletDeliveryStateEnum::IN->value);
            $table->dateTimeTz('in_at')->nullable();
            $table->dateTimeTz('out_at')->nullable();
            $table->jsonb('data')->nullable();

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('pallet_deliveries');
    }
};
