<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot\UI;

use App\Models\Comms\Mailshot;
use Lorisleiva\Actions\Concerns\AsObject;

class GetMailshotPreview
{
    use AsObject;

    public function handle(Mailshot $mailshot): array
    {
        return [
            'layout' => $mailshot->email?->snapshot?->layout
        ];
    }
}