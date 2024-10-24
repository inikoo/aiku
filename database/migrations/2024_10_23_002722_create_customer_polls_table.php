<?php

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('customer_polls', function (Blueprint $table) {
            $table->id();
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->string('name');
            $table->string('label');
            $table->boolean('in_registration');
            $table->boolean('in_registration_required');
            $table->string('type');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('customer_polls');
    }
};
