<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-16h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\Favourite;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Reminder\BackInStockReminder;
use Lorisleiva\Actions\ActionRequest;

class DeleteBackInStockReminder extends OrgAction
{
    use WithActionUpdate;

    private BackInStockReminder $BackInStockReminder;

    public function handle(BackInStockReminder $BackInStockReminder): BackInStockReminder
    {
        $BackInStockReminder->delete();

        return $BackInStockReminder;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function action(BackInStockReminder $BackInStockReminder): BackInStockReminder
    {

        $this->asAction       = true;
        $this->initialisation($BackInStockReminder->organisation, []);

        return $this->handle($BackInStockReminder);
    }


}
