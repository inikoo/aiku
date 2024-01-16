<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\DispatchedEmail\StoreDispatchedEmail;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Mail\Email;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class SendMailshotTest
{
    use AsCommand;
    use AsAction;
    use WithSendMailshot;

    public function handle(Mailshot $mailshot, array $modelData): Collection
    {
        $layout        = $mailshot->layout;

        $emailHtmlBody = $layout['html']['html'];

        $dispatchedEmails = [];
        foreach (Arr::get($modelData, 'emails', []) as $email) {
            $email           = Email::firstOrCreate(['address' => $email]);
            $dispatchedEmail = StoreDispatchedEmail::run($email, $mailshot, [
                'is_test'   => true,
                'outbox_id' => Outbox::where('type', OutboxTypeEnum::TEST)->pluck('id')->first()

            ]);
            $dispatchedEmail->refresh();


            $unsubscribeUrl = route('org.unsubscribe.mailshot.show', $dispatchedEmail->ulid);

            $dispatchedEmails[] = $this->sendEmailWithMergeTags(
                $dispatchedEmail,
                $mailshot->sender(),
                $mailshot->subject,
                $emailHtmlBody,
                $unsubscribeUrl,
            );


        }

        return collect($dispatchedEmails);
    }

    public function jsonResponse($dispatchedEmails): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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

    public function asController(Mailshot $mailshot, ActionRequest $request): Collection
    {
        $request->validate();

        return $this->handle($mailshot, $request->validated());
    }

}
