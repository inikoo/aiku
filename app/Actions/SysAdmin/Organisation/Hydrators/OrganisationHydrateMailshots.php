<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Mail\Mailshot;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateMailshots
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_mailshots'            => Mailshot::count(),
        ];

        foreach (MailshotTypeEnum::cases() as $case) {
            $stats["number_mailshots_type_{$case->snake()}"] = Mailshot::where('type', $case->value)->count();
        }

        foreach (MailshotStateEnum::cases() as $case) {
            $stats["number_mailshots_state_{$case->snake()}"] = Mailshot::where('state', $case->value)->count();
        }

        foreach (MailshotTypeEnum::cases() as $caseType) {
            foreach (MailshotStateEnum::cases() as $caseState) {
                $stats["number_mailshots_type_{$caseType->snake()}_state_{$caseState->snake()}"] = Mailshot::where([['state', $caseState->value], ['type', $caseType->value]])->count();
            }
        }


        $organisation->mailStats()->update($stats);
    }

}
