<?php

use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(ServiceStateEnum::IN_PROCESS)->index();
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedSmallInteger('number_historic_outerables')->default(0);

            $table->string('auto_assign_action')->nullable()->comment('Used for auto assign this service to a action');
            $table->string('auto_assign_action_type')->nullable()->comment('Used for auto assign this service to an action type');


            $table->decimal('price', 18)->nullable();
            $table->string('unit')->nullable();

            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletes();
            $table->string('source_id')->nullable()->unique();
            $table->string('historic_source_id')->nullable()->unique();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
