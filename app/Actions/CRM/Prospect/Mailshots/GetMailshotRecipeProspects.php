<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Leads\Prospect\Mailshots;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\GetMailshotRecipientsQueryBuilder;
use App\Models\Mail\Mailshot;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetMailshotRecipeProspects extends InertiaAction
{
    use AsObject;

    public function handle(Mailshot $mailshot)
    {
        if(Arr::get($mailshot->recipients_recipe, 'recipient_builder_type')!='prospects') {
            return [];
        }
        $queryBuilder=GetMailshotRecipientsQueryBuilder::run($mailshot);
        return $queryBuilder->get();

    }

    public function asController(Mailshot $mailshot, ActionRequest $request)
    {
        return $this->handle($mailshot);
    }

}
