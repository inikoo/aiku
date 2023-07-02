<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Nov 2022 12:28:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\fromIris;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Auth\WebUser;
use App\Models\Inventory\Stock;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateCustomerStockFromIris extends fromIris
{
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            [
                'code'        => ['required', 'alpha_dash'],
                'description' => ['sometimes', 'nullable', 'string', 'max:10000']
            ]
        );
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(WebUser $webUser, array $modelData): ?Stock
    {
        if ($webUser->customer->stocks()->where('code', Arr::get($modelData, 'code'))->exists()) {
            throw  ValidationException::withMessages([
                                                         'code' => 'Stock code already exists'
                                                     ]);
        }

        return StoreStock::run($webUser->customer, $modelData);
    }

    public function jsonResponse(?Stock $stock): StockResource
    {
        if (!$stock) {
            abort(500, 'Could not create stock');
        }

        return new StockResource($stock);
    }
}
