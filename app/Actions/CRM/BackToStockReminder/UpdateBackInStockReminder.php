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
use App\Models\Reminder\BackInStockReminder;
use Lorisleiva\Actions\ActionRequest;

class UpdateBackInStockReminder extends OrgAction
{
    use WithActionUpdate;

    private BackInStockReminder $BackInStockReminder;

    public function handle(BackInStockReminder $BackInStockReminder, array $modelData): BackInStockReminder
    {
        $BackInStockReminder = $this->update($BackInStockReminder, $modelData, ['data']);

        return $BackInStockReminder;
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

    public function action(BackInStockReminder $BackInStockReminder, array $modelData, int $hydratorsDelay = 0, bool $strict = true): BackInStockReminder
    {
        $this->strict = $strict;

        $this->asAction       = true;
        $this->BackInStockReminder       = $BackInStockReminder;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($BackInStockReminder->organisation, $modelData);

        return $this->handle($BackInStockReminder, $this->validatedData);
    }


}
