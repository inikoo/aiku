<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Apr 2023 10:14:43 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasMailshotsStats
{
    public function mailshotsStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_mailshots')->default(0);

        foreach (MailshotStateEnum::cases() as $state) {
            $table->unsignedInteger('number_post_room_state_'.$state->snake())->default(0);
        }




        return $table;
    }
}
