<?php

use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasGroupOrganisationRelationship;
    
    public function up(): void
    {
        Schema::create('purges', function (Blueprint $table) {
            $table->id();
            $table = $this->groupOrgRelationship($table);
            $table->string('state')->default(PurgeStateEnum::IN_PROCESS);
            $table->string('type')->default(PurgeTypeEnum::SCHEDULED);
            $table->unsignedInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->dateTimeTz('date');
            $table->dateTimeTz('start_purge_date');
            $table->dateTimeTz('end_purge_date');
            $table->unsignedSmallInteger('inactive_days');
            $table->unsignedMediumInteger('estimated_orders');
            $table->unsignedMediumInteger('estimated_transactions');
            $table->decimal('estimated_amount', 18,2);
            $table->unsignedInteger('number_orders');
            $table->unsignedInteger('number_purged_orders');
            $table->unsignedInteger('number_purged_transactions');
            $table->decimal('purged_amount', 18,2);
            $table->unsignedMediumInteger('number_purge_exculpated');
            $table->unsignedMediumInteger('number_purge_cancelled');
            $table->unsignedMediumInteger('number_purge_errors');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purges');
    }
};
