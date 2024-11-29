<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 13:30:40 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailBulkRun;

use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateEmailBulkRuns;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Comms\EmailBulkRun\EmailBulkRunTypeEnum;
use App\Enums\Comms\EmailBulkRun\EmailBulkRunStateEnum;
use App\Models\Comms\EmailBulkRun;
use App\Models\Comms\Outbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreEmailBulkRun extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox $outbox, array $modelData): EmailBulkRun
    {
        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);
        data_set($modelData, 'shop_id', $outbox->shop_id);


        $emailBulkRun = DB::transaction(function () use ($outbox, $modelData) {
            /** @var EmailBulkRun $emailRun */
            $emailRun = $outbox->emailBulkRuns()->create($modelData);
            $emailRun->stats()->create();
            $emailRun->intervals()->create();

            return $emailRun;
        });


        OutboxHydrateEmailBulkRuns::dispatch($outbox)->delay($this->hydratorsDelay);

        return $emailBulkRun;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return false;
    }


    public function rules(): array
    {
        $rules = [
            'subject'           => ['required', 'string', 'max:255'],
            'type'              => ['sometimes', 'required', Rule::enum(EmailBulkRunTypeEnum::class)],
            'state'             => ['required', Rule::enum(EmailBulkRunStateEnum::class)],
        ];

        if (!$this->strict) {
            $rules['scheduled_at']     = ['nullable', 'date'];
            $rules['start_sending_at'] = ['nullable', 'date'];
            $rules['sent_at']          = ['nullable', 'date'];
            $rules['stopped_at']       = ['nullable', 'date'];


            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Outbox $outbox, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailBulkRun
    {


        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($outbox->shop, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }


}
