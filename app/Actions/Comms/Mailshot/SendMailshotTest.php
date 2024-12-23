<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\Comms\DispatchedEmail\StoreDispatchedEmail;
use App\Actions\Comms\Traits\WithSendBulkEmails;
use App\Actions\OrgAction;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Comms\Outbox;
use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class SendMailshotTest extends OrgAction
{
    use AsCommand;
    use AsAction;
    use WithSendBulkEmails;

    public function handle(Outbox $outbox, array $modelData): Collection
    {
        $dispatchedEmails = [];

        $parent = StoreMailshot::run($outbox, [
            'subject' => $outbox->emailOngoingRun->email->subject,
            'email_id' => $outbox->emailOngoingRun->email_id,
        ]);
        $recipients = User::whereIn('email', Arr::get($modelData, 'emails', []))->get();

        foreach ($recipients as $recipient) {
            $dispatchedEmail = StoreDispatchedEmail::run($parent, $recipient, [
                'is_test'   => true,
                'outbox_id' => Outbox::where('type', OutboxTypeEnum::TEST)->pluck('id')->first()

            ]);
            $dispatchedEmail->refresh();

            $emailHtmlBody = GetHtmlLayout::run($parent, $dispatchedEmail);

            $unsubscribeUrl = route('org.unsubscribe.mailshot.show', $dispatchedEmail->ulid);

            $this->sendEmailWithMergeTags(
                $dispatchedEmail,
                $parent->sender(),
                $parent->subject,
                $emailHtmlBody,
                $unsubscribeUrl,
            );

            $dispatchedEmails[] = $dispatchedEmail;
        }

        return collect($dispatchedEmails);
    }

    public function jsonResponse($dispatchedEmails): AnonymousResourceCollection
    {
        return DispatchedEmailResource::collection($dispatchedEmails);
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('emails')) {
            $request->merge([
                'emails' =>
                    array_map('trim', explode(",", $request->get('emails')))
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'emails'   => ['required', 'array'],
            'emails.*' => 'required|email:rfc,dns'
        ];
    }

    public function asController(Outbox $outbox, ActionRequest $request): Collection
    {
        $this->initialisationFromShop($outbox->shop, $request);

        return $this->handle($outbox, $this->validatedData);
    }
}
