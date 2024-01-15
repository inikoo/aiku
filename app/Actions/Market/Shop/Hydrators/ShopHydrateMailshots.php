<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:34 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\Hydrators;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Market\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateMailshots
{
    use AsAction;


    public function handle(Shop $shop): void
    {
        $stats = [
            'number_mailshots' => $shop->mailshots()->count(),
        ];

        foreach (MailshotTypeEnum::cases() as $case) {
            $stats["number_mailshots_type_{$case->snake()}"] = $shop->mailshots()->where('type', $case->value)->count();
        }

        foreach (MailshotStateEnum::cases() as $case) {
            $stats["number_mailshots_state_{$case->snake()}"] = $shop->mailshots()->where('state', $case->value)->count();
        }

        foreach (MailshotTypeEnum::cases() as $caseType) {
            foreach (MailshotStateEnum::cases() as $caseState) {
                $stats["number_mailshots_type_{$caseType->snake()}_state_{$caseState->snake()}"] =
                    $shop->mailshots()
                        ->where('type', $caseType->value)
                        ->where('state', $caseState)
                        ->count();
            }
        }
        $shop->mailStats()->update($stats);
    }


}
