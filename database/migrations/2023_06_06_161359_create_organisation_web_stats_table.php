<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Stubs\Migrations\HasWebStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasWebStats;
    public function up(): void
    {
        Schema::create('organisation_web_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('number_websites')->default(0);
            $table->unsignedInteger('number_websites_under_maintenance')->default(0);

            foreach (WebsiteTypeEnum::cases() as $websiteType) {
                $table->unsignedInteger('number_websites_type_'.$websiteType->snake())->default(0);
            }
            foreach (WebsiteStateEnum::cases() as $websiteState) {
                $table->unsignedInteger('number_websites_state_'.$websiteState->snake())->default(0);
            }
            foreach (WebsiteEngineEnum::cases() as $websiteEngine) {
                $table->unsignedInteger('number_websites_engine_'.$websiteEngine->snake())->default(0);
            }

            $table=$this->webStats($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organisation_web_stats');
    }
};
