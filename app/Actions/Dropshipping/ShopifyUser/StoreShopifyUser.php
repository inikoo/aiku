<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\ShopifyUser;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Ordering\Platform;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShopifyUser extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(Customer $customer, $modelData)
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'username', Str::random(4));
        data_set($modelData, 'password', Str::random(8));

        /** @var \App\Models\ShopifyUser $shopifyUser */
        $customer->shopifyUser()->create($modelData);
        // $shopifyUser->api()->getRestClient()->request('GET', '/admin/products', []);
        /*$webUser->customer->platforms()->attach([
            Platform::where('type', PlatformTypeEnum::SHOPIFY->value)->first() => [
                'group_id' => $webUser->group_id,
                'organisation_id' => $webUser->organisation_id
            ]
        ]);*/
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'ends_with:.myshopify.com']
        ];
    }

    public function prepareForValidation(\Lorisleiva\Actions\ActionRequest $request): void
    {
        $this->set('name', 'aikuu.myshopify.com');
    }

    public function asController(Customer $customer, ActionRequest $request)
    {
        $this->initialisationFromShop($customer->shop, $request);

        $this->handle($customer, $this->validatedData);
    }
}
