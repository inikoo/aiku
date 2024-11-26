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
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Models\Comms\Email;
use App\Models\Comms\EmailBulkRun;
use App\Models\Comms\EmailOngoingRun;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Mailshot;
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
    public function handle(Mailshot|EmailBulkRun|EmailOngoingRun $parent, ?EmailTemplate $emailTemplate, array $modelData): Email
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);
        data_set($modelData, 'outbox_id', $parent->outbox_id);
        data_set($modelData, 'subject', $parent->subject);


        $snapshotData = null;
        if (!$this->strict) {
            $snapshotData = [
                'layout'          => Arr::pull($modelData, 'layout'),
                'compiled_layout' => Arr::pull($modelData, 'compiled_layout'),
                'state'           => Arr::pull($modelData, 'snapshot_state'),
                'recyclable'      => Arr::pull($modelData, 'snapshot_recyclable'),
                'first_commit'    => Arr::pull($modelData, 'snapshot_first_commit'),
            ];

            if ($publishedAt = Arr::pull($modelData, 'snapshot_published_at')) {
                $snapshotData['published_at'] = $publishedAt;
            }


            /** @var EmailBuilderEnum $builder */
            $builder = Arr::get($modelData, 'builder');

            if ($builder) {
                $snapshotData['builder'] = $builder->value;
            }
        }

        if ($emailTemplate) {
            data_set($modelData, 'builder', $emailTemplate->builder->value);

            $snapshotData['builder'] = $emailTemplate->builder->value;
            $snapshotData['layout']  = $emailTemplate->layout;
            // dd($modelData);
        }

        return DB::transaction(function () use ($parent, $modelData, $snapshotData) {
            /** @var Email $email */
            $email = $parent->email()->create($modelData);

            if ($snapshotData) {
                $snapshot = StoreEmailSnapshot::make()->action(
                    $email,
                    $snapshotData,
                    strict: $this->strict,
                );
                $email->update(
                    [
                        'snapshot_id' => $snapshot->id,
                    ]
                );
            }

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

        ];

        if (!$this->strict) {
            $rules['builder']               = ['sometimes', 'required', Rule::enum(EmailBuilderEnum::class)];
            $rules['layout']                = ['sometimes', 'required', 'array'];
            $rules['compiled_layout']       = ['sometimes', 'string'];
            $rules['snapshot_state']        = ['sometimes', 'required', Rule::enum(SnapshotStateEnum::class)];
            $rules['snapshot_published_at'] = ['sometimes', 'required', 'date'];
            $rules['snapshot_recyclable']   = ['sometimes', 'required', 'boolean'];
            $rules['snapshot_first_commit'] = ['sometimes', 'required', 'boolean'];

            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Mailshot|EmailBulkRun|EmailOngoingRun $parent, ?EmailTemplate $emailTemplate, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Email
    {
        if (!$audit) {
            Email::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($parent->organisation, $modelData);

        return $this->handle($parent, $emailTemplate, $this->validatedData);
    }


}
