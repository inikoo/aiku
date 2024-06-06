<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasWebStats
{
    public function getWebsitesStatsFields(Blueprint $table): Blueprint
    {

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


        return $table;
    }


    public function getWebpagesStatsFields(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_webpages')->default(0);
        foreach (WebpageStateEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_webpages_state_'.$case->snake())->default(0);
        }
        foreach (WebpageTypeEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_webpages_type_'.$case->snake())->default(0);
        }
        foreach (WebpagePurposeEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_webpages_purpose_'.$case->snake())->default(0);
        }


        return $table;
    }
}
