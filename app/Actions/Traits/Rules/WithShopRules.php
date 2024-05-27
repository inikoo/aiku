<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 11:32:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Rules;

use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Validation\Rule;

trait WithShopRules
{
    protected function getStoreShopRules(): array
    {
        return [
            'code' => [
                'required',
                'max:4',
                'alpha_dash',
                new IUnique(
                    table: 'shops',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),


            ],
            'name' => ['required', 'string', 'max:255'],

            'contact_name'             => ['nullable', 'string', 'max:255'],
            'company_name'             => ['nullable', 'string', 'max:255'],
            'email'                    => ['nullable', 'email'],
            'phone'                    => 'nullable',
            'identity_document_number' => ['nullable', 'string'],
            'identity_document_type'   => ['nullable', 'string'],
            'state'                    => ['sometimes', 'required', Rule::in(ShopStateEnum::values())],
            'type'                     => ['required', Rule::in(ShopTypeEnum::values())],
            'country_id'               => ['required', 'exists:countries,id'],
            'currency_id'              => ['required', 'exists:currencies,id'],
            'language_id'              => ['required', 'exists:languages,id'],
            'timezone_id'              => ['required', 'exists:timezones,id'],
            'closed_at'                => ['sometimes', 'nullable', 'date'],
            'settings'                 => ['sometimes', 'array'],
            'created_at'               => ['sometimes', 'date'],
            'source_id'                => ['sometimes', 'string'],
            'warehouses'               => ['sometimes', 'array'],
            'warehouses.*'             => ['exists:warehouses,id'],
            'address'                  => ['sometimes','required', new ValidAddress()],

        ];
    }
}
