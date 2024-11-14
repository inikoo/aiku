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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable()->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('warehouse_id')->nullable()->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedSmallInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('origin_source');
            $table->string('origin_type')->index();
            $table->unsignedInteger('origin_id');
            $table->index(['origin_type', 'origin_id']);
            $table->dateTimeTz('date');
            $table->boolean('supplier')->default(false);
            $table->boolean('picker')->default(false);
            $table->boolean('packer')->default(false);
            $table->boolean('warehouse')->default(false);
            $table->boolean('courier')->default(false);
            $table->boolean('marketing')->default(false);
            $table->boolean('customer')->default(false);
            $table->boolean('other')->default(false);
            $table->text('message');
            $table->jsonb('data');
            $table->dateTimeTz('audited_at')->nullable();
            $table->datetimeTz('fetched_at')->nullable();
            $table->datetimeTz('last_fetched_at')->nullable();
            $table->string('source_id')->nullable()->unique();
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
