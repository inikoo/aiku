<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\Mail\Outbox\Hydrators\OutboxHydrateEmails;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Email;
use App\Models\Mail\Mailshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDispatchedEmail
{
    use AsAction;

    public function handle(Email $email, ?Mailshot $mailshot, array $modelData=[]): DispatchedEmail
    {

        data_set($modelData, 'outbox_id', $mailshot?->outbox_id, overwrite: false);

        /** @var DispatchedEmail $dispatchedEmail */
        $dispatchedEmail = DispatchedEmail::create(
            array_merge([
                'email_id'    => $email->id,
                'ulid'        => Arr::get($modelData, 'ulid', Str::ulid()),
                'date'        => now(),
                'mailshot_id' => $mailshot?->id
            ], $modelData)
        );

        if($dispatchedEmail->outbox_id) {
            OutboxHydrateEmails::dispatch($dispatchedEmail->outbox)->delay(60);
        }

        $dispatchedEmail->refresh();

        return $dispatchedEmail;
    }
}
