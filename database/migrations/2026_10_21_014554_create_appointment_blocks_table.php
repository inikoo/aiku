<?php

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('appointment_blocks', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('appointment_type_id')->nullable()->index();
            $table->foreign('appointment_type_id')->references('id')->on('appointment_types')->cascadeOnDelete();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->boolean('is_all_day')->default(false);
            $table->string('reason')->nullable();
            $table->unsignedSmallInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampsTz();
            $table->index(['shop_id', 'starts_at', 'ends_at']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('appointment_blocks');
    }
};
