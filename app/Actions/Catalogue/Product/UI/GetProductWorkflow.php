<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Web\SeoContentSuggestion;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Where a shop product is on its way to a good webpage: created and published by the webmaster,
 * reviewed by the SEO team, and the SEO team's suggestions implemented by the webmaster.
 */
class GetProductWorkflow
{
    use AsObject;

    /**
     * @return array{current: int, label: string, steps: array<int, array{key: string, label: string, owner: string, done: bool}>}
     */
    public function handle(Product $product): array
    {
        return $this->forProducts(collect([$product]))[$product->id];
    }

    /**
     * @param Collection<int, Product> $products
     *
     * @return array<int, array{current: int, label: string, steps: array<int, array{key: string, label: string, owner: string, done: bool}>}> keyed by product id
     */
    public function forProducts(Collection $products): array
    {
        $reviews = $this->seoReviews($products);

        return $products->mapWithKeys(function (Product $product) use ($reviews) {
            $isReviewed = $product->webpage_id && $reviews->has($product->webpage_id);

            return [$product->id => $this->workflow([
                ['key' => 'created', 'label' => __('Product created'), 'owner' => __('Webmaster'), 'done' => true],
                ['key' => 'published', 'label' => __('Published'), 'owner' => __('Webmaster'), 'done' => (bool)$product->has_live_webpage],
                ['key' => 'seo_review', 'label' => __('SEO review & suggestions'), 'owner' => __('SEO team'), 'done' => $isReviewed],
                ['key' => 'suggestions_implemented', 'label' => __('Suggestions implemented'), 'owner' => __('Webmaster'), 'done' => $isReviewed && !$reviews->get($product->webpage_id)],
            ])];
        })->all();
    }

    private function workflow(array $steps): array
    {
        $currentIndex = collect($steps)->search(fn (array $step) => !$step['done']);
        if ($currentIndex === false) {
            $currentIndex = count($steps) - 1;
        }

        return [
            'current' => $currentIndex + 1,
            'label'   => $steps[$currentIndex]['label'],
            'steps'   => $steps,
        ];
    }

    /**
     * Whether each reviewed webpage still has a suggestion to implement or validate, keyed by webpage id.
     *
     * @param Collection<int, Product> $products
     *
     * @return Collection<int, bool>
     */
    private function seoReviews(Collection $products): Collection
    {
        $webpageIds = $products->pluck('webpage_id')->filter()->values();

        if ($webpageIds->isEmpty()) {
            return collect();
        }

        return SeoContentSuggestion::whereIn('webpage_id', $webpageIds)
            ->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW)
            ->groupBy('webpage_id')
            ->selectRaw('webpage_id, bool_or(state in (?, ?)) as is_open', [SeoContentSuggestionStateEnum::PENDING->value, SeoContentSuggestionStateEnum::IMPLEMENTED->value])
            ->pluck('is_open', 'webpage_id')
            ->map(fn ($isOpen) => (bool)$isOpen);
    }
}
