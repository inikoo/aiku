<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Market\Shop;

use App\Actions\Market\Shop\Hydrators\ShopHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Enums\Marketing\Shop\ShopSubtypeEnum;
use App\Enums\Marketing\Shop\ShopTypeEnum;
use App\Http\Resources\Marketing\ShopResource;
use App\Models\Marketing\Shop;
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
            'code'                     => ['sometimes', 'required', 'unique:tenant.shops', 'between:2,4', 'alpha_dash'],
            'contact_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'                    => ['sometimes', 'nullable', 'email'],
            'phone'                    => 'sometimes|nullable',
            'identity_document_number' => ['sometimes', 'nullable', 'string'],
            'identity_document_type'   => ['sometimes', 'nullable', 'string'],
            'type'                     => ['sometimes', 'required', Rule::in(ShopTypeEnum::values())],
            'subtype'                  => ['sometimes', 'required', Rule::in(ShopSubtypeEnum::values())],
            'currency_id'              => ['sometimes', 'required', 'exists:central.currencies,id'],
            'language_id'              => ['sometimes', 'required', 'exists:central.languages,id'],
            'timezone_id'              => ['sometimes', 'required', 'exists:central.timezones,id'],
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


    public function action(Shop $shop, $objectData): Shop
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }

    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }

}
