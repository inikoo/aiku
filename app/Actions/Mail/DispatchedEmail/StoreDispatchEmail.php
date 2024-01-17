<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\Mail\EmailAddress\GetEmailAddress;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreDispatchEmail
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(Outbox|Mailshot $parent, string $email, array $modelData): DispatchedEmail
    {
        if (class_basename($parent)=='Mailshot') {
            $modelData['outbox_id']=$parent->outbox_id;
        }

        $emailAddress                 =GetEmailAddress::run($email);
        $modelData['email_address_id']=$emailAddress->id;
        /** @var DispatchedEmail $dispatchedEmail */
        $dispatchedEmail= $parent->dispatchedEmails()->create($modelData);
        return $dispatchedEmail;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("mail.edit");
    }
    public function rules(): array
    {
        return [
        ];
    }

    public function action(Outbox|Mailshot $parent, string $email, array $modelData): DispatchedEmail
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $email, $validatedData);
    }
}
