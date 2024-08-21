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
use App\Models\CRM\WebUser;
use App\Models\Dropshipping\Platform;
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

        $customer->shopifyUser()->create($modelData);

        $customer->platforms()->attach([
            Platform::where('type', PlatformTypeEnum::SHOPIFY->value)->first() => [
                'group_id'        => $customer->group_id,
                'organisation_id' => $customer->organisation_id
            ]
        ]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction || $request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'ends_with:.' . config('shopify-app.myshopify_domain')]
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->set('name', $request->input('name').'.'.config('shopify-app.myshopify_domain'));
    }

    public function asController(ActionRequest $request)
    {
        $customer = $request->user()->customer;
        $this->initialisationFromShop($customer->shop, $request);

        $this->handle($customer, $this->validatedData);
    }
}
