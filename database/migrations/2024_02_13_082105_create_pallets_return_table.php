<?php

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasSoftDeletes;

    public function up()
    {
        Schema::create('pallet_returns', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->ulid()->unique()->index();
            $table->unsignedSmallInteger('fulfilment_customer_id');
            $table->foreign('fulfilment_customer_id')->references('id')->on('fulfilment_customers');
            $table->unsignedSmallInteger('fulfilment_id');
            $table->foreign('fulfilment_id')->references('id')->on('fulfilments');
            $table->unsignedSmallInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->string('customer_reference')->nullable()->index();
            $table->string('reference')->unique()->index();
            $table->unsignedSmallInteger('number_pallets')->default(0);
            $table->unsignedSmallInteger('number_pallet_stored_items')->default(0);
            $table->unsignedSmallInteger('number_stored_items')->default(0);
            $table->string('state')->default(PalletReturnStateEnum::IN_PROCESS->value);
            $table->dateTimeTz('booked_in_at')->nullable();
            $table->dateTimeTz('settled_at')->nullable();
            foreach (PalletReturnStateEnum::cases() as $state) {
                $table->dateTimeTz("{$state->snake()}_at")->nullable();
            }
            $table->dateTimeTz('dispatched_at')->nullable();
            $table->dateTimeTz('date')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestampsTz();
            $this->softDeletes($table);
        });
    }


    public function down()
    {
        Schema::dropIfExists('return_pallets');
    }
};
