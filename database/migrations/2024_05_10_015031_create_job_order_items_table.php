<?php

use App\Enums\Manufacturing\JobOrder\JobOrderStateEnum;
use App\Enums\Manufacturing\JobOrderItem\JobOrderItemStatusEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasGroupOrganisationRelationship;
    use HasSoftDeletes;

    public function up()
    {
        Schema::create('job_order_items', function (Blueprint $table) {
            $table->id();
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('job_order_id')->index();
            $table->foreign('job_order_id')->references('id')->on('job_orders');
            $table->unsignedInteger('artifact_id')->index();
            $table->foreign('artifact_id')->references('id')->on('artifacts');
            $table->string('slug')->nullable()->unique()->collation('und_ns');
            $table->string('reference')->nullable()->index()->collation('und_ci');
            $table->string('status')->index()->default(JobOrderItemStatusEnum::RECEIVING->value);
            $table->string('state')->index()->default(JobOrderStateEnum::IN_PROCESS->value);
            $table->text('notes')->nullable();
            $table->integer('quantity');
            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('booking_in_at')->nullable();
            $table->dateTimeTz('set_as_not_received_at')->nullable();
            $table->dateTimeTz('booked_in_at')->nullable();
            $table->dateTimeTz('storing_at')->nullable();
            $table->dateTimeTz('requested_for_return_at')->nullable();
            $table->dateTimeTz('picking_at')->nullable();
            $table->dateTimeTz('picked_at')->nullable();
            $table->dateTimeTz('set_as_incident_at')->nullable();
            $table->dateTimeTz('dispatched_at')->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table = $this->softDeletes($table);
        });
    }


    public function down()
    {
        Schema::dropIfExists('job_order_items');
    }
};
