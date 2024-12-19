<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\Comms\DispatchedEmail\StoreDispatchedEmail;
use App\Actions\OrgAction;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Comms\Email;
use App\Models\Comms\EmailBulkRun;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
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

    public function handle(Mailshot|EmailBulkRun $parent, array $modelData): Collection
    {
        $dispatchedEmails = [];
        foreach (Arr::get($modelData, 'emails', []) as $email) {
            $email           = Email::firstOrCreate(['address' => $email]);
            $dispatchedEmail = StoreDispatchedEmail::run($email, $parent, [
                'is_test'   => true,
                'outbox_id' => Outbox::where('type', OutboxTypeEnum::TEST)->pluck('id')->first()

            ]);
            $dispatchedEmail->refresh();

            GetHtmlLayout::run($parent, $dispatchedEmail);

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

    public function asController(Mailshot $mailshot, ActionRequest $request): Collection
    {
        $this->initialisationFromShop($mailshot->shop, $request);

        return $this->handle($mailshot, $this->validatedData);
    }
}
