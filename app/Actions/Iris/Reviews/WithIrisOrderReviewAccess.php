<?php

namespace App\Actions\Iris\Reviews;

use App\Enums\Catalogue\Review\ReviewScopeEnum;
use App\Models\Catalogue\Product;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Models\Reviews\Review;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait WithIrisOrderReviewAccess
{
    protected function ensureReviewLink(Order $order, string $token): void
    {
        abort_unless($order->shop_id === $this->shop->id && hash_equals($order->reviewToken(), $token), 404);
    }

    /**
     * @return Collection<int, Product>
     */
    protected function orderedProducts(Order $order): Collection
    {
        $productIds = Transaction::query()
            ->where('order_id', $order->id)
            ->where('model_type', 'Product')
            ->distinct()
            ->pluck('model_id');

        return Product::query()->whereIn('id', $productIds)->orderBy('code')->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function visitorOrderReview(Order $order): array
    {
        $reviews = Review::query()
            ->where('order_id', $order->id)
            ->whereIn('scope', [ReviewScopeEnum::ORDER->value, ReviewScopeEnum::PRODUCT->value])
            ->get();

        $orderReview = $reviews->firstWhere('scope', ReviewScopeEnum::ORDER);

        return [
            'id'        => $order->id,
            'token'     => $order->reviewToken(),
            'reference' => $order->reference,
            'date'      => $order->dispatched_at?->translatedFormat('j F Y') ?? $order->date?->translatedFormat('j F Y'),
            'company'   => [
                'scope'         => ReviewScopeEnum::ORDER->value,
                'reviewable_id' => $order->id,
                'name'          => $order->shop->name,
                'logo'          => $order->shop->image ? $order->shop->imageSources(120, 120) : null,
                'rating'        => $orderReview ? (int) round($orderReview->rating_main) : null,
                'message'       => $orderReview?->message,
            ],
            'products'  => $this->orderedProducts($order)->map(function (Product $product) use ($reviews) {
                $productReview = $reviews->first(fn (Review $review) => $review->scope === ReviewScopeEnum::PRODUCT && $review->product_id === $product->id);

                return [
                    'scope'         => ReviewScopeEnum::PRODUCT->value,
                    'reviewable_id' => $product->id,
                    'code'          => $product->code,
                    'name'          => $product->name,
                    'image'         => $product->image ? $product->imageSources(160, 160) : null,
                    'rating'        => $productReview ? (int) round($productReview->rating_main) : null,
                    'message'       => $productReview?->message,
                ];
            })->values()->all(),
        ];
    }

    /**
     * @return array{product: int|null, rating: int|null}
     */
    protected function preselected(array $query): array
    {
        $rating = (int) Arr::get($query, 'rating');

        return [
            'product' => ((int) Arr::get($query, 'product')) ?: null,
            'rating'  => $rating >= 1 && $rating <= 5 ? $rating : null,
        ];
    }
}
