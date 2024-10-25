<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:18 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Actions\Dropshipping\Portfolio\StorePortfolio;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Portfolio\PortfolioTypeEnum;
use App\Models\WooCommerceUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProductWooCommerce extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(WooCommerceUser $wooCommerceUser, array $modelData)
    {
        DB::transaction(function () use ($wooCommerceUser, $modelData) {
            foreach (Arr::get($modelData, 'products') as $product) {
                $portfolio = StorePortfolio::run($wooCommerceUser->customer, [
                    'product_id' => $product,
                    'type' => PortfolioTypeEnum::WOOCOMMERCE->value,
                ]);

                // HandleApiProductToWooCommerce::dispatch($wooCommerceUser, [$portfolio->id]);
            }
        });
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array']
        ];
    }

    public function asController(WooCommerceUser $wooCommerceUser, ActionRequest $request): void
    {
        $this->initialisationFromShop($wooCommerceUser->customer->shop, $request);

        $this->handle($wooCommerceUser, $this->validatedData);
    }
}
