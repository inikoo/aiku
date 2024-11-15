<?php
/*
 * author Arya Permana - Kirin
 * created on 14-11-2024-11h-36m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Helpers\Feedback;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Helpers\Feedback\FeedbackOriginSourceEnum;
use App\Models\Helpers\Feedback;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateFeedback extends OrgAction
{
    use WithNoStrictRules;
    use WithActionUpdate;


    public function handle(Feedback $feedback, array $modelData): Feedback
    {
        return $this->update($feedback, $modelData);
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
            'origin_source'     => ['sometimes', Rule::enum(FeedbackOriginSourceEnum::class)],
            'date'              => ['sometimes', 'date'],
            'message'           => ['sometimes', 'string'],
            'blame_supplier'          => ['sometimes', 'boolean'],
            'blame_picker'            => ['sometimes', 'boolean'],
            'blame_packer'            => ['sometimes', 'boolean'],
            'blame_warehouse'         => ['sometimes', 'boolean'],
            'blame_courier'           => ['sometimes', 'boolean'],
            'blame_marketing'         => ['sometimes', 'boolean'],
            'blame_customer'          => ['sometimes', 'boolean'],
            'blame_other'             => ['sometimes', 'boolean'],
        ];

        if (!$this->strict) {
            $rules['message'] = ['sometimes', 'nullable', 'string'];
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    public function action(Feedback $feedback, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Feedback
    {
        if (!$audit) {
            Feedback::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($feedback->organisation, $modelData);

        return $this->handle($feedback, $this->validatedData);
    }

}
