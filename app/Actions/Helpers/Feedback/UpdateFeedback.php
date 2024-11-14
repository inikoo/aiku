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

    /**
     * @throws \Throwable
     */
    public function handle(Feedback $feedback, array $modelData): Feedback
    {

        $feedback = $this->update($feedback, $modelData);

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
            'origin_source'     => ['sometimes', Rule::enum(FeedbackOriginSourceEnum::class)],
            'date'              => ['sometimes', 'date'],
            'message'           => ['sometimes', 'string'],
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
