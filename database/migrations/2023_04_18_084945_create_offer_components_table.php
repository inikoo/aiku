<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 16:54:17 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Discounts\OfferComponent\OfferComponentStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('offer_components', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('offer_campaign_id')->index();
            $table->foreign('offer_campaign_id')->references('id')->on('offer_campaigns');
            $table->unsignedSmallInteger('offer_id')->index();
            $table->foreign('offer_id')->references('id')->on('offers');
            $table->string('state')->default(OfferComponentStateEnum::IN_PROCESS->value)->index();
            $table->boolean('status')->default(false)->index();


            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code');
            $table->jsonb('data');
            $table->string('trigger_scope')->nullable()->index();
            $table->string('trigger_type')->index();
            $table->string('trigger_id')->nullable();
            $table->string('target_type')->nullable()->index();




            $table->timestampsTz();

            $table->datetimeTz('start_at')->nullable();
            $table->datetimeTz('end_at')->nullable();
            $table->datetimeTz('fetched_at')->nullable();
            $table->datetimeTz('last_fetched_at')->nullable();
            $table->string('source_id')->nullable()->unique();

            $table->softDeletesTz();

            $table->index(['trigger_type','trigger_id']);
            $table->index(['trigger_type','trigger_id','trigger_scope']);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('offer_components');
    }
};
