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
use Illuminate\Database\Schema\Blueprint;

trait HasWebStats
{
    public function webStats(Blueprint $table): Blueprint
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
