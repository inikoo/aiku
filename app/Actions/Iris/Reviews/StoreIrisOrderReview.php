<?php

namespace App\Actions\Iris\Reviews;

use App\Actions\IrisAction;
use App\Actions\Reviews\StoreReview;
use App\Actions\Reviews\UpdateReview;
use App\Enums\Catalogue\Review\ReviewScopeEnum;
use App\Models\Ordering\Order;
use App\Models\Reviews\Review;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class StoreIrisOrderReview extends IrisAction
{
    use WithIrisOrderReviewAccess;

    /**
     * @param  array{reviews: array<int, array{scope: string, reviewable_id: int, rating: int, message?: string|null}>}  $modelData
     *
     * @throws \Throwable
     */
    public function handle(Order $order, array $modelData): Order
    {
        $products = $this->orderedProducts($order)->keyBy('id');

        foreach ($modelData['reviews'] as $index => $reviewData) {
            $isProduct = $reviewData['scope'] === ReviewScopeEnum::PRODUCT->value;

            if ($isProduct && !$products->has($reviewData['reviewable_id'])) {
                throw ValidationException::withMessages(["reviews.$index.reviewable_id" => __('This product is not in the order.')]);
            }

            $existingReview = Review::query()
                ->where('order_id', $order->id)
                ->where('scope', $reviewData['scope'])
                ->when($isProduct, fn ($query) => $query->where('product_id', $reviewData['reviewable_id']))
                ->first();

            $review = [
                'rating'  => $reviewData['rating'],
                'message' => Arr::get($reviewData, 'message'),
            ];

            if ($existingReview) {
                UpdateReview::run($existingReview, $review);
                continue;
            }

            StoreReview::make()->action($isProduct ? $products->get($reviewData['reviewable_id']) : $order, [
                ...$review,
                'customer_id' => $order->customer_id,
                'order_id'    => $order->id,
                'is_public'   => true,
            ]);
        }

        return $order;
    }

    public function rules(): array
    {
        return [
            'reviews'                 => ['required', 'array', 'min:1'],
            'reviews.*.scope'         => ['required', Rule::in([ReviewScopeEnum::ORDER->value, ReviewScopeEnum::PRODUCT->value])],
            'reviews.*.reviewable_id' => ['required', 'integer'],
            'reviews.*.rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'reviews.*.message'       => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Order $order, string $token, ActionRequest $request): Order
    {
        $this->initialisation($request);
        $this->ensureReviewLink($order, $token);

        return $this->handle($order, $this->validatedData);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonResponse(Order $order): array
    {
        return $this->visitorOrderReview($order);
    }
}
