<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('model_has_offer_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->unsignedBigInteger('offer_component_id');
            $table->foreign('offer_component_id')->references('id')->on('offer_components');
            $table->unsignedBigInteger('offer_id');
            $table->foreign('offer_id')->references('id')->on('offers');
            $table->unsignedBigInteger('offer_campaign_id');
            $table->foreign('offer_campaign_id')->references('id')->on('offer_campaigns');
            $table->dateTimeTz('fetched_at')->nullable();
            $table->string('source_id')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_has_offer_components');
    }
};
