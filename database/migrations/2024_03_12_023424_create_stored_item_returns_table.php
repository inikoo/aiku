<?php

use App\Enums\Fulfilment\StoredItemReturn\StoredItemReturnStateEnum;
use App\Stubs\Migrations\HasFulfilmentDelivery;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasSoftDeletes;
    use HasFulfilmentDelivery;

    public function up()
    {
        Schema::create('stored_item_returns', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->getPalletIOFields($table);
            $table->string('state')->default(StoredItemReturnStateEnum::IN_PROCESS->value);

            foreach (StoredItemReturnStateEnum::cases() as $state) {
                $table->dateTimeTz("{$state->snake()}_at")->nullable();
            }
            $table->dateTimeTz('date')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestampsTz();
            $this->softDeletes($table);
        });
    }


    public function down()
    {
        Schema::dropIfExists('stored_item_returns');
    }
};
