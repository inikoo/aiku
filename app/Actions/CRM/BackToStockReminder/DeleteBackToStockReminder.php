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
use App\Models\CRM\Favourite;
use App\Models\Reminder\BackToStockReminder;
use Lorisleiva\Actions\ActionRequest;

class DeleteBackToStockReminder extends OrgAction
{
    use WithActionUpdate;

    private BackToStockReminder $backToStockReminder;

    public function handle(BackToStockReminder $backToStockReminder): BackToStockReminder
    {
        $backToStockReminder->delete();

        return $backToStockReminder;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function action(BackToStockReminder $backToStockReminder): BackToStockReminder
    {

        $this->asAction       = true;
        $this->initialisation($backToStockReminder->organisation, []);

        return $this->handle($backToStockReminder);
    }


}
