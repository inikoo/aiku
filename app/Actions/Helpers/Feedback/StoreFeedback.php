<?php

/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-10h-52m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Helpers\Feedback;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Helpers\Feedback\FeedbackOriginSourceEnum;
use App\Models\Accounting\Invoice;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Helpers\Feedback;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreFeedback extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(DeliveryNote|Invoice|Organisation $origin, array $modelData): Feedback
    {
        data_set($modelData, 'group_id', $origin->group_id);
        data_set($modelData, 'organisation_id', $origin instanceof Organisation ? $origin->id : $origin->organisation_id);

        if ($origin instanceof Invoice || $origin instanceof DeliveryNote) {
            data_set($modelData, 'shop_id', $origin->shop_id);
        }

        return $origin->feedbacks()->create($modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false; //TODO
    }

    public function rules(): array
    {
        $rules = [
            'origin_source'   => ['required', Rule::enum(FeedbackOriginSourceEnum::class)],
            'date'            => ['sometimes', 'required', 'date'],
            'message'         => ['required', 'string'],
            'blame_supplier'  => ['sometimes', 'boolean'],
            'blame_picker'    => ['sometimes', 'boolean'],
            'blame_packer'    => ['sometimes', 'boolean'],
            'blame_warehouse' => ['sometimes', 'boolean'],
            'blame_courier'   => ['sometimes', 'boolean'],
            'blame_marketing' => ['sometimes', 'boolean'],
            'blame_customer'  => ['sometimes', 'boolean'],
            'blame_other'     => ['sometimes', 'boolean'],
            'user_id'         => [
                'required',
                Rule::Exists('users', 'id')->where('group_id', $this->organisation->group_id)
            ],
        ];

        if (!$this->strict) {
            $rules['user_id'] = [
                'sometimes',
                'nullable',
                Rule::Exists('users', 'id')->where('group_id', $this->organisation->group_id)
            ];
            $rules['message'] = ['nullable', 'string'];
            $rules            = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(DeliveryNote|Invoice|Organisation $origin, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Feedback
    {
        if (!$audit) {
            Feedback::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        if ($origin instanceof Organisation) {
            $this->initialisation($origin, $modelData);
        } else {
            $this->initialisation($origin->organisation, $modelData);
        }

        return $this->handle($origin, $this->validatedData);
    }

}
