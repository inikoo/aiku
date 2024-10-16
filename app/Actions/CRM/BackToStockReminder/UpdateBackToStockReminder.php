<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-16h-31m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\Favourite;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Reminder\BackToStockReminder;
use Lorisleiva\Actions\ActionRequest;

class UpdateBackToStockReminder extends OrgAction
{
    use WithActionUpdate;

    private BackToStockReminder $backToStockReminder;

    public function handle(BackToStockReminder $backToStockReminder, array $modelData): BackToStockReminder
    {
        $backToStockReminder = $this->update($backToStockReminder, $modelData, ['data']);

        return $backToStockReminder;
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
        $rules = [];

        if (!$this->strict) {
            $rules['last_fetched_at'] = ['sometimes', 'date'];
        }
        return $rules;

    }

    public function action(BackToStockReminder $backToStockReminder, array $modelData, int $hydratorsDelay = 0, bool $strict = true): BackToStockReminder
    {
        $this->strict = $strict;

        $this->asAction       = true;
        $this->backToStockReminder       = $backToStockReminder;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($backToStockReminder->organisation, $modelData);

        return $this->handle($backToStockReminder, $this->validatedData);
    }


}
