<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\WithActionUpdate;
use App\Enums\Marketing\Shop\ShopSubtypeEnum;
use App\Enums\Marketing\Shop\ShopTypeEnum;
use App\Models\Marketing\Shop;
use Illuminate\Validation\Rule;

class UpdateShop
{
    use WithActionUpdate;

    public function handle(Shop $shop, array $modelData): Shop
    {
        return $this->update($shop, $modelData, ['data', 'settings']);
    }

    public function rules(): array
    {
        return [
            'name'                     => ['required', 'string', 'max:255'],
            'code'                     => ['required', 'unique:tenant.shops', 'between:2,4', 'alpha_dash'],
            'contact_name'             => ['nullable', 'string', 'max:255'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => ['nullable', 'email'],
            'phone'                    => 'nullable',
            'identity_document_number' => ['nullable', 'string'],
            'identity_document_type'   => ['nullable', 'string'],
            'type'                     => ['required', Rule::in(ShopTypeEnum::values())],
            'subtype'                  => ['required', Rule::in(ShopSubtypeEnum::values())],
            'currency_id'              => ['required', 'exists:central.currencies,id'],
            'language_id'              => ['required', 'exists:central.languages,id'],
            'timezone_id'              => ['required', 'exists:central.timezones,id'],
        ];
    }

    public function action(Shop $shop, array $objectData): Shop
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
