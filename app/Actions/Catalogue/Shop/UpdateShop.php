<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateShops;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateShops;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Catalogue\ShopResource;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateShop extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(Shop $shop, array $modelData): Shop
    {
        if (Arr::exists($modelData, 'address')) {
            $addressData = Arr::get($modelData, 'address');
            Arr::forget($modelData, 'address');
            $shop = $this->updateModelAddress($shop, $addressData);
        }

        foreach ($modelData as $key => $value) {
            data_set(
                $modelData,
                match ($key) {
                    'shopify_shop_url'        => 'settings.shopify.shop_url',
                    'shopify_api_key'         => 'settings.shopify.api_key',
                    'shopify_api_secret'      => 'settings.shopify.api_secret',
                    'shopify_access_token'    => 'settings.shopify.access_token',
                    default                   => $key
                },
                $value
            );

            data_forget($modelData, $key);
        }

        if (Arr::exists($modelData, 'collection_address')) {
            $collectionAddressData = Arr::get($modelData, 'collection_address');
            Arr::forget($modelData, 'collection_address');

            if ($shop->collection_address_id) {
                UpdateAddress::run($shop->collectionAddress, $collectionAddressData);
            } else {
                return $this->addAddressToModel(model: $shop, addressData: $collectionAddressData, updateLocation: false, updateAddressField: 'collection_address_id');
            }
        }


        $shop    = $this->update($shop, $modelData, ['data', 'settings']);
        $changes = $shop->getChanges();

        if (Arr::hasAny($changes, ['state'])) {
            GroupHydrateShops::dispatch($shop->group);
            OrganisationHydrateShops::dispatch($shop->organisation);
        }
        if (count($changes) > 0) {
            ShopHydrateUniversalSearch::dispatch($shop);
        }

        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return true;

        // Need fix this
        return $request->user()->hasPermissionTo("shops.view." . $this->shop->id);
    }

    public function rules(): array
    {
        return [
            'name'                     => ['sometimes', 'required', 'string', 'max:255'],
            'code'                     => [
                'sometimes',
                'required',
                'between:2,4',
                'alpha_dash',
                new IUnique(
                    table: 'shops',
                    extraConditions: [

                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->shop->id
                        ],
                    ]
                ),

            ],
            'contact_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'                    => ['sometimes', 'nullable', 'email'],
            'phone'                    => ['sometimes', 'nullable'],
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'identity_document_type'   => ['sometimes', 'nullable', 'string'],
            'type'                     => ['sometimes', 'required', Rule::enum(ShopTypeEnum::class)],
            'currency_id'              => ['sometimes', 'required', 'exists:currencies,id'],
            'language_id'              => ['sometimes', 'required', 'exists:languages,id'],
            'timezone_id'              => ['sometimes', 'required', 'exists:timezones,id'],
            'address'                  => ['sometimes', 'required', new ValidAddress()],
            'collection_address'       => ['sometimes', 'required', new ValidAddress()],
            'state'                    => ['sometimes', Rule::enum(ShopStateEnum::class)],
            'shopify_shop_url'         => ['sometimes', 'string'],
            'shopify_api_key'          => ['sometimes', 'string'],
            'shopify_api_secret'       => ['sometimes', 'string'],
            'shopify_access_token'     => ['sometimes', 'string']
        ];
    }

    public function action(Shop $shop, $modelData): Shop
    {
        $this->asAction = true;
        $this->shop     = $shop;

        $this->initialisation($shop->organisation, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->shop = $shop;
        $this->initialisation($organisation, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }

}
