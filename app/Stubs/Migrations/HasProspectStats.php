<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 04 Oct 2023 19:55:13 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Enums\Miscellaneous\GenderEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasProspectStats
{
    public function prospectsStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_prospects')->default(0);

        foreach (ProspectStateEnum::cases() as $prospectState) {
            $table->unsignedInteger("number_prospects_state_{$prospectState->snake()}")->default(0);
        }

        foreach (GenderEnum::cases() as $case) {
            $table->unsignedSmallInteger('number_prospects_gender_'.$case->snake())->default(0);
        }


        foreach (ProspectContactedStateEnum::cases() as $case) {
            $table->unsignedInteger("number_prospects_contacted_state_{$case->snake()}")->default(0);
        }

        foreach (ProspectFailStatusEnum::cases() as $case) {
            $table->unsignedInteger("number_prospects_fail_status_{$case->snake()}")->default(0);
        }

        foreach (ProspectSuccessStatusEnum::cases() as $case) {
            $table->unsignedInteger("number_prospects_success_status_{$case->snake()}")->default(0);
        }


        $table->unsignedInteger("number_prospects_dont_contact_me")->default(0);




        return $table;
    }




}
