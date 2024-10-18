<?php

use App\Enums\Catalogue\Shop\ShopStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('master_shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->index()->collation('und_ns');
            $table->string('name', 255)->nullable();
            $table->string('state')->index()->default(ShopStateEnum::IN_PROCESS->value);
            $table->string('type')->index();
            $table->unsignedInteger('image_id')->nullable();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_shops');
    }
};
