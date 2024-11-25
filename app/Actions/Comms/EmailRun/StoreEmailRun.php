<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailRun;

use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateEmailRuns;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Comms\Email\EmailRunStateEnum;
use App\Enums\Comms\EmailRun\EmailRunTypeEnum;
use App\Models\Comms\EmailRun;
use App\Models\Comms\Outbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreEmailRun extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox $outbox, array $modelData): EmailRun
    {
        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);
        data_set($modelData, 'shop_id', $outbox->shop_id);


        $emailRun = DB::transaction(function () use ($outbox, $modelData) {
            /** @var EmailRun $emailRun */
            $emailRun = $outbox->emailRuns()->create($modelData);
            $emailRun->stats()->create();

            return $emailRun;
        });


        OutboxHydrateEmailRuns::dispatch($outbox)->delay($this->hydratorsDelay);

        return $emailRun;
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
            'type'              => ['sometimes', 'required', Rule::enum(EmailRunTypeEnum::class)],
            'state'             => ['required', Rule::enum(EmailRunStateEnum::class)],
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
    public function action(Outbox $outbox, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailRun
    {


        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($outbox->shop, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }


}
