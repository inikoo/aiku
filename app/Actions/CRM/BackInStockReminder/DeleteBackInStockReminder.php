<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-16h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\Favourite;

use App\Actions\Catalogue\Product\Hydrators\ProductHydrateCustomersWhoReminded;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateCustomersWhoReminded;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateBackInStockReminders;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Reminder\BackInStockReminder;
use Lorisleiva\Actions\ActionRequest;

class DeleteBackInStockReminder extends OrgAction
{
    use WithActionUpdate;

    private BackInStockReminder $backInStockReminder;

    public function handle(BackInStockReminder $backInStockReminder): BackInStockReminder
    {
        $backInStockReminder->delete();

        CustomerHydrateBackInStockReminders::run($this->backInStockReminder->customer);
        ProductHydrateCustomersWhoReminded::run($this->backInStockReminder->product);
        ProductCategoryHydrateCustomersWhoReminded::run($this->backInStockReminder->product);

        return $backInStockReminder;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function action(BackInStockReminder $backInStockReminder): BackInStockReminder
    {
        $this->backInStockReminder = $backInStockReminder;
        $this->asAction       = true;
        $this->initialisation($backInStockReminder->organisation, []);

        return $this->handle($backInStockReminder);
    }


}
