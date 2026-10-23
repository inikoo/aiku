<?php

/*
 * author Louis Perez
 * created on 20-11-2025-15h-21m
 * github: https://github.com/louis-perez
 * copyright 2025
*/

namespace App\Actions\Iris\Catalogue;

use App\Actions\Catalogue\Product\GetProductIncomingStock;
use App\Actions\IrisAction;
use App\Actions\Web\WebBlock\Concerns\HasWebBlockProductAttachments;
use App\Enums\Discounts\Offer\OfferStateEnum;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Discounts\Offer\OfferTypeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Http\Resources\Traits\HasPriceMetrics;
use App\Models\Catalogue\Product;
use App\Models\Discounts\Offer;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use App\Helpers\NaturalLanguage;
use App\Http\Resources\Catalogue\TagResource;

class GetProductDetail extends IrisAction
{
    use HasPriceMetrics;
    use HasWebBlockProductAttachments;

    public function handle(Product $product): Product
    {
        return $product;
    }

    private function getStepDiscount(Product $product): ?array
    {
        $offer = Offer::where('trigger_type', 'Product')
            ->where('trigger_id', $product->id)
            ->where('type', OfferTypeEnum::PRODUCT_QUANTITY_ORDERED->value)
            ->where('state', OfferStateEnum::ACTIVE->value)
            ->where('status', true)
            ->first();

        if (!$offer) {
            return null;
        }

        $allowance = $offer->offerAllowances()->where('status', true)->first();
        $steps     = Arr::get($allowance?->data, 'steps', []);

        if (empty($steps)) {
            return null;
        }

        $tiers = collect($steps)
            ->sortBy('min_quantity')
            ->map(function (array $step) use ($product) {
                $percentageOff   = (float)Arr::get($step, 'percentage_off', 0);
                $discountedPrice = round($product->price * (1 - $percentageOff), 2);

                [, , , , , $pricePerUnit] = $this->getPriceMetrics($product->rrp, $discountedPrice, $product->units);

                return [
                    'min_quantity'         => (int)Arr::get($step, 'min_quantity', 1),
                    'percentage_off'       => $percentageOff,
                    'percentage_off_label' => percentage($percentageOff, 1),
                    'price'                => $discountedPrice,
                    'price_per_unit'       => $pricePerUnit,
                    'is_popular'           => (bool)Arr::get($step, 'is_popular', false),
                ];
            })
            ->values()
            ->all();

        return [
            'label' => $offer->label ?? $offer->name,
            'unit'  => $product->unit,
            'units' => (int)$product->units,
            'steps' => $tiers,
        ];
    }

    public function jsonResponse(Product $product, ActionRequest $request): array
    {

        $bestPercentageOff            = Arr::get($product->offers_data, 'best_percentage_off.percentage_off', 0);
        $bestPercentageOffOfferFactor = 1 - (float)$bestPercentageOff;
        $countriesOrigin = [];
        $countries      = array_filter(array_map('trim', explode(',', $product->country_of_origin ?? '')));
        foreach ($countries as $country) {
            $countriesOrigin[] = NaturalLanguage::make()->country($country);
        }

        $tradeUnits = $product->tradeUnits;

        $ingredients = $product->marketing_ingredients;
        $dimensions  = $this->dimensions($product->marketing_dimensions);
        $cpnp        = $product->cpnp_number;
        $components  = [];
        $attachments = $this->getProductAttachments($product->id);

        if ($tradeUnits->count() > 1) {
            $ingredients = null;
            $dimensions  = null;
            $cpnp        = null;
            [$components, $componentAttachments] = $this->components($product, $tradeUnits);
            $attachments = array_merge($attachments, $componentAttachments);
        }

        $specifications = [
            'countries_of_origin' => $countriesOrigin,
            'ingredients'         => $ingredients,
            'gross_weight'        => $product->gross_weight,
            'barcode'             => $product->barcode,
            'dimensions'          => $dimensions,
            'cpnp'                => $cpnp,
            'marketing_weight'    => $product->marketing_weight,
            'unit'                => $product->unit,
        ];

        $isStockShown         = (bool) data_get($product->shop->settings, 'catalog.allow_stocks_to_be_shown_on_iris', true);
        $isIncomingStockShown = (bool) data_get($product->shop->settings, 'catalog.allow_incoming_stocks_to_be_shown_on_iris', true);

        /** @noinspection PhpUnusedLocalVariableInspection */
        [$marginDiscounted, $rrpPerUnitDiscounted, $profitDiscounted, $profitPerUnitDiscounted, $unitsDiscounted, $pricePerUnitDiscounted] = $this->getPriceMetrics($product->rrp, $bestPercentageOffOfferFactor * $product->price, $product->units);

        return [
            'discounted_price'           => round($product->price * $bestPercentageOffOfferFactor, 2),
            'discounted_price_per_unit'  => $pricePerUnitDiscounted,
            'discounted_profit'          => $profitDiscounted,
            'discounted_profit_per_unit' => $profitPerUnitDiscounted,
            'discounted_margin'          => $marginDiscounted,
            'discounted_percentage'      => percentage($bestPercentageOff, 1),
            'offers_data'                => $product->offers_data,
            'step_discount'              => $this->getStepDiscount($product),
            'tags'                       => TagResource::collection($product->tags)->toArray($request),
            'specifications'             => $specifications,
            'components'                 => $components,
            'attachments'                => $attachments,
            'allow_stocks_to_be_shown_on_iris'          => $isStockShown,
            'allow_incoming_stocks_to_be_shown_on_iris' => $isIncomingStockShown,
            'incoming_stock'                            => $isIncomingStockShown ? GetProductIncomingStock::run($product, true) : [],
        ];

    }

    private function dimensions(mixed $marketingDimensions): ?string
    {
        return blank($marketingDimensions) ? null : NaturalLanguage::make()->dimensions(json_encode($marketingDimensions));
    }

    /**
     * A product made of several trade units, a bundle or a starter, lists its components
     * instead of merging their ingredients, sizes and CPNP numbers into one unreadable
     * value. Each component links to the page of the product sold on its own in the same
     * shop, where its full specification is; one without such a page keeps its ingredients,
     * and the documents of its single product that is not for sale are shown on this page.
     * A single product counts as having a page by the same rule the product page uses to
     * show it, so a component never links to a page that returns 404.
     *
     * @return array{0: array<int, array{code: string, name: string, quantity: float, url: ?string, ingredients: ?string}>, 1: array<int, array<string, mixed>>}
     */
    private function components(Product $product, Collection $tradeUnits): array
    {
        $componentProducts = Product::where('shop_id', $product->shop_id)
            ->where('is_single_trade_unit', true)
            ->whereIn('state', [ProductStateEnum::ACTIVE, ProductStateEnum::DISCONTINUING])
            ->whereHas('tradeUnits', fn ($query) => $query->whereIn('trade_units.id', $tradeUnits->pluck('id')))
            ->with(['webpage', 'tradeUnits'])
            ->orderBy('id')
            ->get();

        $productByTradeUnit       = [];
        $hiddenProductByTradeUnit = [];
        foreach ($componentProducts as $componentProduct) {
            $hasVisiblePage = ($componentProduct->is_for_sale || $componentProduct->is_variant_leader)
                && $componentProduct->webpage?->state === WebpageStateEnum::LIVE;
            foreach ($componentProduct->tradeUnits as $tradeUnit) {
                if ($hasVisiblePage) {
                    $productByTradeUnit[$tradeUnit->id] ??= $componentProduct;
                } else {
                    $hiddenProductByTradeUnit[$tradeUnit->id] ??= $componentProduct;
                }
            }
        }

        $components  = [];
        $attachments = [];
        foreach ($tradeUnits->sortBy('code', SORT_NATURAL | SORT_FLAG_CASE) as $tradeUnit) {
            /** @var Product|null $componentProduct */
            $componentProduct = $productByTradeUnit[$tradeUnit->id] ?? null;

            $components[] = [
                'code'        => $componentProduct?->code ?? $tradeUnit->code,
                'name'        => $componentProduct?->name ?? $tradeUnit->name,
                'quantity'    => (float)$tradeUnit->pivot->quantity,
                'url'         => $componentProduct?->webpage->getCanonicalUrl(),
                'ingredients' => $componentProduct || blank($tradeUnit->marketing_ingredients) ? null : $tradeUnit->marketing_ingredients,
            ];

            $hiddenProduct = $componentProduct ? null : ($hiddenProductByTradeUnit[$tradeUnit->id] ?? null);
            if ($hiddenProduct) {
                foreach ($this->getProductAttachments($hiddenProduct->id) as $attachment) {
                    $attachment['caption'] = trim($tradeUnit->name.' '.($attachment['caption'] ?? ''));
                    $attachments[]         = $attachment;
                }
            }
        }

        return [$components, $attachments];
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $this->initialisation($request);
        return $this->handle($product);
    }

}
