<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-16h-24m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\BackToStockReminder;

use App\Actions\Catalogue\Product\Hydrators\ProductHydrateCustomersWhoFavourited;
use App\Actions\Catalogue\ProductCategory\Hydrators\ProductCategoryHydrateCustomersWhoFavourited;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateFavourites;
use App\Actions\OrgAction;
use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\CRM\Favourite;
use App\Models\Reminder\BackToStockReminder;
use Lorisleiva\Actions\ActionRequest;

class StoreBackToStockReminder extends OrgAction
{
    public function handle(Customer $customer, Product $product, array $modelData): BackToStockReminder
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);
        data_set($modelData, 'product_id', $product->id);
        data_set($modelData, 'department_id', $product->department_id);
        data_set($modelData, 'sub_department_id', $product->sub_department_id);
        data_set($modelData, 'family_id', $product->family_id);


        /** @var BackToStockReminder $reminder */
        $reminder = $customer->backToStockReminder()->create($modelData);

        // CustomerHydrateFavourites::run($customer);
        // ProductHydrateCustomersWhoFavourited::run($product);
        // ProductCategoryHydrateCustomersWhoFavourited::run($product);

        return $reminder;
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
            $rules['source_id']  = ['sometimes', 'string', 'max:64'];
            $rules['fetched_at'] = ['sometimes', 'date'];
            $rules['created_at'] = ['sometimes', 'date'];
        }

        return $rules;
    }


    public function action(Customer $customer, Product $product, array $modelData, int $hydratorsDelay = 0, bool $strict = true): BackToStockReminder
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $product, $this->validatedData);
    }


}