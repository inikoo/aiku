<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\OrgAction;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateUniversalSearch;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateMarket;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Catalogue\ShopResource;
use App\Models\Catalogue\Shop;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateShop extends OrgAction
{
    use WithActionUpdate;

    public function handle(Shop $shop, array $modelData): Shop
    {
        $shop =  $this->update($shop, $modelData, ['data', 'settings']);
        ShopHydrateUniversalSearch::dispatch($shop);
        if ($shop->wasChanged(['type', 'state'])) {
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
            'code'                     => ['sometimes', 'required', 'between:2,4', 'alpha_dash',
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
            'phone'                    => ['sometimes','nullable'],
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'identity_document_type'   => ['sometimes', 'nullable', 'string'],
            'type'                     => ['sometimes', 'required', Rule::in(ShopTypeEnum::values())],
            'currency_id'              => ['sometimes', 'required', 'exists:currencies,id'],
            'language_id'              => ['sometimes', 'required', 'exists:languages,id'],
            'timezone_id'              => ['sometimes', 'required', 'exists:timezones,id'],
        ];
    }

    public function action(Shop $shop, $modelData): Shop
    {
        $this->asAction = true;
        $this->shop     = $shop;

        $this->initialisation($shop->organisation, $modelData);

        return $this->handle($shop, $this->validatedData);
    }

    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $this->shop = $shop;
        $this->initialisation($shop->organisation, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }

}
