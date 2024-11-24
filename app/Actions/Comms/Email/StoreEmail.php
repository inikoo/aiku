<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Comms\Email;

use App\Actions\Helpers\Snapshot\StoreEmailSnapshot;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Comms\Email\EmailBuilderEnum;
use App\Models\Comms\Email;
use App\Models\Comms\EmailRun;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreEmail extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox $outbox, Mailshot|EmailRun|Outbox $parent, array $modelData): Email
    {
        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);
        data_set($modelData, 'shop_id', $outbox->shop_id);


        data_set($modelData, 'parent_id', $parent->id);
        data_set($modelData, 'parent_type', class_basename($parent));

        $layout = Arr::pull($modelData, 'layout', []);

        return DB::transaction(function () use ($outbox, $modelData, $layout) {
            /** @var Email $email */
            $email    = $outbox->emails()->create($modelData);
            $snapshot = StoreEmailSnapshot::run(
                $email,
                [
                    'builder' => $email->builder->value,
                    'layout'  => $layout
                ],
            );
            $email->update(
                [
                    'unpublished_snapshot_id' => $snapshot->id,
                ]
            );


            return $email;
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
            'subject' => ['required', 'string', 'max:255'],
            'builder' => ['required', Rule::enum(EmailBuilderEnum::class)],
            'layout'  => ['sometimes', 'array']
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Outbox $outbox, Mailshot|EmailRun|Outbox $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Email
    {
        if (!$audit) {
            Email::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisation($outbox->organisation, $modelData);

        return $this->handle($outbox, $parent, $this->validatedData);
    }


}
