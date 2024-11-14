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
use App\Models\Inventory\Location;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreFeedback extends OrgAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(DeliveryNote|Invoice|Organisation $parent, array $modelData): Feedback
    {
        data_set($modelData, 'group_id', $parent->group_id);

        data_set($modelData, 'organisation_id', $parent instanceof Organisation ? $parent->id : $parent->organisation_id);
        
        if ($parent instanceof Invoice || $parent instanceof DeliveryNote) {
            data_set($modelData, 'shop_id', $parent->shop_id);
        }

        $feedback = $parent->feedbacks()->create($modelData);

        return $feedback;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return true; //TODO
    }

    public function rules(): array
    {
        $rules = [
            'origin_source'     => ['required', Rule::enum(FeedbackOriginSourceEnum::class)],
            'date'              => ['sometimes', 'required', 'date'],
            'message'           => ['required', 'string'],
            'supplier'          => ['sometimes', 'boolean'],
            'picker'            => ['sometimes', 'boolean'],
            'packer'            => ['sometimes', 'boolean'],
            'warehouse'         => ['sometimes', 'boolean'],
            'courier'           => ['sometimes', 'boolean'],
            'marketing'         => ['sometimes', 'boolean'],
            'customer'          => ['sometimes', 'boolean'],
            'other'             => ['sometimes', 'boolean'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(DeliveryNote|Invoice|Organisation $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Feedback
    {
        if (!$audit) {
            Feedback::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        if($parent instanceof Organisation){
            $this->initialisation($parent, $modelData);
        } else {
            $this->initialisation($parent->organisation, $modelData);
        }

        return $this->handle($parent, $this->validatedData);
    }

}
