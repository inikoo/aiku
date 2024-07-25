<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class GetMailshotMergeTags
{
    use AsAction;
    use WithAttributes;


    public function handle(Mailshot $mailshot): array
    {

        return [
            [
                'name'  => __('Name'),
                'value' => '{{name}}'
            ],
        ];
    }

    public function asController(Mailshot $mailshot): array
    {
        return $this->handle($mailshot);
    }
}
