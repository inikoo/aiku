<?php

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasGroupOrganisationRelationship;

    public function up(): void
    {

        Schema::create('model_subscribed_to_outboxes', function (Blueprint $table) {
            $table->id();
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->string('model_type')->index();
            $table->unsignedInteger('model_id');
            $table->unsignedSmallInteger('outbox_id')->index();
            $table->foreign('outbox_id')->references('id')->on('outboxes');
            $table->jsonb('data');
            $table->dateTimeTz('unsubscribed_at');
            $table->index(['model_type', 'model_id']);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_subscribed_to_outboxes');
    }
};
