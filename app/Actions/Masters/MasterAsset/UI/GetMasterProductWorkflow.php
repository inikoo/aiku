<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Masters\MasterAsset\UI;

use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Masters\MasterAsset;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Where a master product is on its way to the shops' websites, read from the products of
 * its open shops. The SEO review and its suggestions are not recorded anywhere yet, so
 * those two stages stay pending until they are.
 */
class GetMasterProductWorkflow
{
    use AsObject;

    /**
     * @param Collection<int, Product>|null $products the shop products to follow, by default every product in an open shop
     *
     * @return array{current: int, label: string, steps: array<int, array{key: string, label: string, done: bool}>}|null
     */
    public function handle(MasterAsset $masterAsset, ?Collection $products = null): ?array
    {
        if ($products === null && !$masterAsset->relationLoaded('products')) {
            return null;
        }

        $products ??= $this->productsInOpenShops($masterAsset);

        $isCreated   = $products->isNotEmpty();
        $isPublished = $isCreated && $products->every(fn (Product $product) => $product->has_live_webpage);

        $steps = [
            ['key' => 'created', 'label' => __('Product created'), 'done' => $isCreated],
            ['key' => 'published', 'label' => __('Published'), 'done' => $isPublished],
            ['key' => 'seo_review', 'label' => __('SEO review & suggestions'), 'done' => false],
            ['key' => 'suggestions_implemented', 'label' => __('Suggestions implemented'), 'done' => false],
        ];

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
     * Shops of one organisation share its stock, so each organisation is counted once.
     */
    public function stock(MasterAsset $masterAsset): ?float
    {
        if (!$masterAsset->relationLoaded('products')) {
            return null;
        }

        $stockByOrganisation = $this->productsInOpenShops($masterAsset)
            ->filter(fn (Product $product) => $product->available_quantity !== null)
            ->groupBy('organisation_id')
            ->map(fn (Collection $products) => (float)$products->max('available_quantity'));

        return $stockByOrganisation->isEmpty() ? null : $stockByOrganisation->sum();
    }

    /**
     * @return Collection<int, Product>
     */
    private function productsInOpenShops(MasterAsset $masterAsset): Collection
    {
        return $masterAsset->products->filter(
            fn (Product $product) => $product->shop && $product->shop->state != ShopStateEnum::CLOSED
        );
    }
}
