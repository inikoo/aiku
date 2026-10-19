<?php

namespace App\Actions\Iris\Reviews;

use App\Actions\IrisAction;
use App\Enums\Catalogue\Review\ReviewScopeEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * The review page a test review reminder links to: the shop's real products under a made-up order,
 * where Send thanks the tester but stores nothing, so trying the email never writes a review.
 */
class ShowIrisOrderReviewPreview extends IrisAction
{
    use WithIrisOrderReviewAccess;

    /**
     * @param  array<int, int>  $productIds
     * @param  array{product?: int, rating?: int}  $preselect
     */
    public static function url(Shop $shop, string $reference, array $productIds, array $preselect = []): ?string
    {
        $domain = $shop->website?->domain;

        if (!$domain) {
            return null;
        }

        $query = ['reference' => $reference, 'products' => implode(',', $productIds), ...$preselect];

        return 'https://'.$domain.'/review/preview/'.self::token($shop, $query['reference'], $query['products']).'?'.http_build_query($query);
    }

    public static function token(Shop $shop, string $reference, string $products): string
    {
        return hash_hmac('sha256', 'order-review-preview:'.$shop->id.':'.$reference.':'.$products, (string) config('app.key'));
    }

    /**
     * @param  array<int, int>  $productIds
     *
     * @return array<string, mixed>
     */
    public function handle(Shop $shop, string $reference, array $productIds): array
    {
        $products = Product::query()
            ->where('shop_id', $shop->id)
            ->whereIn('id', $productIds)
            ->orderBy('code')
            ->get();

        return [
            'id'        => 0,
            'token'     => '',
            'reference' => $reference,
            'date'      => now()->translatedFormat('j F Y'),
            'company'   => [
                'scope'         => ReviewScopeEnum::ORDER->value,
                'reviewable_id' => 0,
                'name'          => $shop->name,
                'logo'          => $shop->image ? $shop->imageSources(120, 120) : null,
                'rating'        => null,
                'message'       => null,
            ],
            'products'  => $products->map(fn (Product $product) => [
                'scope'         => ReviewScopeEnum::PRODUCT->value,
                'reviewable_id' => $product->id,
                'code'          => $product->code,
                'name'          => $product->name,
                'image'         => $product->image ? $product->imageSources(160, 160) : null,
                'rating'        => null,
                'message'       => null,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(string $token, ActionRequest $request): array
    {
        $this->initialisation($request);

        $reference = (string) $request->query('reference');
        $products  = (string) $request->query('products');

        abort_unless($reference !== '' && hash_equals(self::token($this->shop, $reference, $products), $token), 404);

        return $this->handle($this->shop, $reference, array_map('intval', array_filter(explode(',', $products))));
    }

    /**
     * @param  array<string, mixed>  $order
     */
    public function htmlResponse(array $order, ActionRequest $request): Response
    {
        return Inertia::render('OrderReview', [
            'title'     => __('Review your order'),
            'order'     => $order,
            'preselect' => $this->preselected(Arr::only($request->query(), ['product', 'rating'])),
            'isPreview' => true,
        ]);
    }
}
