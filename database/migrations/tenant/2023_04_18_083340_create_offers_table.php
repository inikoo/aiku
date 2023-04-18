<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('offer_campaign_id')->index();
            $table->foreign('offer_campaign_id')->references('id')->on('offer_campaigns');
            $table->string('slug');
            $table->string('code');
            $table->string('name');
            $table->jsonb('data');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
