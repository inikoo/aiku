<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 16:01:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('transaction_has_offer_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->unsignedSmallInteger('offer_campaign_id');
            $table->foreign('offer_campaign_id')->references('id')->on('offer_campaigns');
            $table->unsignedInteger('offer_id');
            $table->foreign('offer_id')->references('id')->on('offers');
            $table->unsignedInteger('offer_component_id');
            $table->foreign('offer_component_id')->references('id')->on('offer_components');
            $table->decimal('discounted_amount', 12, 2)->default(0);
            $table->decimal('discounted_percentage', 6, 4)->default(0);
            $table->string('info')->nullable();
            $table->boolean('is_pinned')->default('false')->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->dateTimeTz('fetched_at')->nullable();
            $table->dateTimeTz('last_fetched_at')->nullable();
            $table->string('source_id')->nullable();
            $table->string('alt_source_id')->nullable();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('transaction_has_offer_components');
    }
};
