<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('master_asset_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('master_asset_id')->index();
            $table->foreign('master_asset_id')->references('id')->on('master_assets')->onDelete('cascade');

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_asset_stats');
    }
};
