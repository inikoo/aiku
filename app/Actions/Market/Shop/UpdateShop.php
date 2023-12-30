<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Market\Shop;

use App\Actions\Market\Shop\Hydrators\ShopHydrateUniversalSearch;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateMarket;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Http\Resources\Market\ShopResource;
use App\Models\Market\Shop;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateShop
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Shop $shop, array $modelData): Shop
    {
        $shop =  $this->update($shop, $modelData, ['data', 'settings']);
        ShopHydrateUniversalSearch::dispatch($shop);
        if ($shop->wasChanged(['type', 'subtype', 'state'])) {
            OrganisationHydrateMarket::dispatch(app('currentTenant'));
        }

        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function rules(): array
    {
        return [
            'name'                     => ['sometimes', 'required', 'string', 'max:255'],
            'code'                     => ['sometimes', 'required', 'unique:shops', 'between:2,4', 'alpha_dash'],
            'contact_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'                    => ['sometimes', 'nullable', 'email'],
            'phone'                    => ['sometimes','nullable'],
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'identity_document_type'   => ['sometimes', 'nullable', 'string'],
            'type'                     => ['sometimes', 'required', Rule::in(ShopTypeEnum::values())],
            'subtype'                  => ['sometimes', 'required', Rule::in(ShopSubtypeEnum::values())],
            'currency_id'              => ['sometimes', 'required', 'exists:currencies,id'],
            'language_id'              => ['sometimes', 'required', 'exists:languages,id'],
            'timezone_id'              => ['sometimes', 'required', 'exists:timezones,id'],
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $this->fillFromRequest($request);

        return $this->handle(
            shop:$shop,
            modelData: $this->validateAttributes()
        );
    }


    public function action(Shop $shop, $modelData): Shop
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }

    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }

}
