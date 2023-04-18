<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 16:54:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('offer_components', function (Blueprint $table) {
            $table->smallIncrements('id');
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
        Schema::dropIfExists('offer_components');
    }
};
