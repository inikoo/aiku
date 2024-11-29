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
        Schema::create('org_post_rooms', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('post_room_id')->index();
            $table->foreign('post_room_id')->references('id')->on('post_rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->index();
            $table->string('name');
            $table->jsonb('data');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('org_post_rooms');
    }
};
