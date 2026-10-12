<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Oct 2026 11:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Iris\Basket;

use App\Actions\Catalogue\Product\Json\GetDiscontinuingProductsInFamily;
use App\Actions\Ordering\Order\UpdateState\SubmitOrder;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Discounts\Offer;
use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

class GetBasketBonusStockPreview
{
    use AsObject;

    /**
     * The free discontinued stock offers a basket is working towards, with the products they would hand out,
     * so the basket can show the bonus stock as its own line before the order is placed. The gift transactions
     * themselves are still only created by SubmitOrder (HELP-3834).
     *
     * @return array<int, array{offer_id: int, label: string, family_code: ?string, family_name: ?string, current: float, target: float, is_reached: bool, is_in_basket: bool, quantity: int, products: array<int, array<string, mixed>>}>
     */
    public function handle(Order $order): array
    {
        $giftMeters = collect($order->offer_meters ?? [])->where('is_gift', true)->keyBy('offer_id');
        if ($giftMeters->isEmpty()) {
            return [];
        }

        $offers = Offer::whereIn('id', $giftMeters->keys())->where('status', true)->get()->keyBy('id');

        $allowancesData = DB::table('offer_allowances')
            ->whereIn('offer_id', $offers->keys())
            ->where('status', true)
            ->whereNotNull(DB::raw("data->>'discontinuing_in_family_id'"))
            ->pluck('data', 'offer_id');

        $bonusStock = [];
        foreach ($allowancesData as $offerId => $allowanceData) {
            $offer         = $offers->get($offerId);
            $giftMeter     = $giftMeters->get($offerId);
            $allowanceData = json_decode($allowanceData, true);
            $quantity      = (int)Arr::get($allowanceData, 'quantity', 0);
            $current       = (float)Arr::get($giftMeter, 'metadata.current', 0);
            $target        = (float)Arr::get($giftMeter, 'metadata.target', 0);

            $family      = ProductCategory::find(Arr::get($allowanceData, 'discontinuing_in_family_id'));
            [$giftLines] = GetDiscontinuingProductsInFamily::make()->getGiftLines(
                $family,
                Arr::get($allowanceData, 'product_id'),
                $quantity
            );

            $bonusStock[] = [
                'offer_id'     => $offer->id,
                'label'        => $offer->name,
                'family_code'  => $family?->code,
                'family_name'  => $family?->name,
                'current'      => $current,
                'target'       => $target,
                'is_reached'   => $target > 0 && $current >= $target,
                'is_in_basket' => SubmitOrder::make()->orderHasGiftFromOffer($order, $offer->id),
                'quantity'     => $quantity,
                'products'     => array_map(fn (array $giftLine) => $this->getGiftProductData(...$giftLine), $giftLines),
            ];
        }

        return $bonusStock;
    }

    private function getGiftProductData(Product $product, int $quantity): array
    {
        return [
            'id'                  => $product->id,
            'code'                => $product->code,
            'name'                => $product->name,
            'units'               => (int)$product->units,
            'quantity'            => $quantity,
            'price'               => $product->price,
            'rrp'                 => $product->rrp,
            'web_image_thumbnail' => Arr::get($product->web_images, 'main.original'),
            'canonical_url'       => $product->webpage_id ? DB::table('webpages')->where('id', $product->webpage_id)->value('url') : null,
        ];
    }
}
