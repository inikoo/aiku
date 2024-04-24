<?php

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up()
    {
        Schema::create('shipping_events', function (Blueprint $table) {
            $table->smallIncrements('id');

            $this->groupOrgRelationship($table);
            $table->morphs('provider');

            $table->dateTimeTz('sent_at');
            $table->dateTimeTz('received_at')->nullable();

            $table->jsonb('events');
            $table->jsonb('data');

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('shipping_events');
    }
};
