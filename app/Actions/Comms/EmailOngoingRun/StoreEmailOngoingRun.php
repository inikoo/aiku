<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 08:14:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailOngoingRun;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Comms\EmailOngoingRun;
use App\Models\Comms\Outbox;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreEmailOngoingRun extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox $outbox, array $modelData): EmailOngoingRun
    {
        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);
        data_set($modelData, 'shop_id', $outbox->shop_id);


        return DB::transaction(function () use ($outbox, $modelData) {
            /** @var EmailOngoingRun $emailRun */
            $emailRun = $outbox->emailOngoingRuns()->create($modelData);
            $emailRun->stats()->create();

            return $emailRun;
        });
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
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Outbox $outbox, array $modelData, int $hydratorsDelay = 0, bool $strict = true): EmailOngoingRun
    {


        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($outbox->shop, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }


}
