<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Masters\MasterAsset\UI;

use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Masters\MasterAsset;
use App\Models\Web\Webpage;
use App\Services\QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * What a master product and the webpages of its shops should carry before it sells well.
 * Product items are SQL expressions over master_assets, so table rows and the "incomplete
 * information" count share one definition. Webpage items are checked on each shop's page:
 * an item is done only when every shop has it.
 */
class GetMasterProductChecklist
{
    use AsObject;

    /**
     * @return array<string, array{label: string, filled: string}>
     */
    public function items(): array
    {
        return [
            'description'           => [
                'label'  => __('Description'),
                'filled' => "coalesce(trim(master_assets.description), '') <> ''",
            ],
            'description_extra'     => [
                'label'  => __('Extra description'),
                'filled' => "coalesce(trim(master_assets.description_extra), '') <> ''",
            ],
            'price'                 => [
                'label'  => __('Price'),
                'filled' => 'coalesce(master_assets.price, 0) > 0',
            ],
            'marketing_weight'      => [
                'label'  => __('Net weight'),
                'filled' => 'coalesce(master_assets.marketing_weight, 0) > 0',
            ],
            'gross_weight'          => [
                'label'  => __('Shipping weight'),
                'filled' => 'coalesce(master_assets.gross_weight, 0) > 0',
            ],
            'marketing_dimensions'  => [
                'label'  => __('Dimensions'),
                'filled' => "coalesce(master_assets.marketing_dimensions::text, 'null') not in ('{}', '[]', 'null')",
            ],
            'marketing_ingredients' => [
                'label'  => __('Material / ingredients'),
                'filled' => "coalesce(trim(master_assets.marketing_ingredients), '') <> ''",
            ],
            'barcode'               => [
                'label'  => __('Barcode'),
                'filled' => "(coalesce(trim(master_assets.barcode), '') <> '' or exists (
                    select 1 from model_has_trade_units
                    join trade_units on trade_units.id = model_has_trade_units.trade_unit_id
                    where model_has_trade_units.model_type = 'MasterAsset'
                      and model_has_trade_units.model_id = master_assets.id
                      and coalesce(trim(trade_units.barcode), '') <> ''
                ))",
            ],
            'origin_country'        => [
                'label'  => __('Origin country'),
                'filled' => "(master_assets.origin_country_id is not null or coalesce(trim(master_assets.country_of_origin), '') <> '')",
            ],
            'image'                 => [
                'label'  => __('Image'),
                'filled' => 'master_assets.image_id is not null',
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, check: \Closure(Webpage): bool}>
     */
    public function webpageItems(): array
    {
        return [
            'page_status'      => [
                'label' => __('Page status'),
                'check' => fn (Webpage $webpage) => $webpage->state == WebpageStateEnum::LIVE,
            ],
            'meta_title'       => [
                'label' => __('Meta title'),
                'check' => fn (Webpage $webpage) => filled($webpage->title),
            ],
            'meta_description' => [
                'label' => __('Meta description'),
                'check' => fn (Webpage $webpage) => filled($webpage->description),
            ],
            'url'              => [
                'label' => __('URL'),
                'check' => fn (Webpage $webpage) => filled($webpage->url),
            ],
            'breadcrumb_label' => [
                'label' => __('Breadcrumb label'),
                'check' => fn (Webpage $webpage) => filled($webpage->breadcrumb_label),
            ],
            'index_page'       => [
                'label' => __('Index page'),
                'check' => fn (Webpage $webpage) => (bool)$webpage->index_page,
            ],
            'follow_link'      => [
                'label' => __('Follow link'),
                'check' => fn (Webpage $webpage) => (bool)$webpage->follow_link,
            ],
        ];
    }

    public function addSelects(EloquentBuilder|Builder|QueryBuilder $query): void
    {
        foreach ($this->items() as $key => $item) {
            $query->selectRaw("({$item['filled']}) as checklist_$key");
        }
    }

    public function whereIncomplete(EloquentBuilder|Builder $query): void
    {
        $query->where(function ($query) {
            foreach ($this->items() as $item) {
                $query->orWhereRaw("not ({$item['filled']})");
            }
        });
    }

    /**
     * One master product with its product checks and its shop products, ready for handle().
     */
    public function checklistRow(MasterAsset $masterAsset): MasterAsset
    {
        $query = MasterAsset::query()->whereKey($masterAsset->id)->select('master_assets.id');
        $this->addSelects($query);

        /** @var MasterAsset */
        return $query->with(['products.shop', 'products.webpage'])->firstOrFail();
    }

    /**
     * @param Collection<int, Product>|null $products the shop products whose webpages are checked, by default every product in an open shop
     *
     * @return array{complete: int, total: int, items: array<int, array{key: string, group: string, label: string, done: bool, missing_in: array<int, string>}>, missing: array<int, string>}|null
     */
    public function handle(Model $row, ?Collection $products = null): ?array
    {
        if (!array_key_exists('checklist_description', $row->getAttributes())) {
            return null;
        }

        $items = [];
        foreach ($this->items() as $key => $item) {
            $items[] = [
                'key'        => $key,
                'group'      => 'product',
                'label'      => $item['label'],
                'done'       => (bool)$row->getAttribute("checklist_$key"),
                'missing_in' => [],
            ];
        }

        $products ??= $this->productsInOpenShops($row);

        if ($products !== null) {
            foreach ($this->webpageItems() as $key => $item) {
                $missingIn = $products
                    ->reject(fn (Product $product) => $product->webpage && ($item['check'])($product->webpage))
                    ->map(fn (Product $product) => $product->shop?->code)
                    ->filter()
                    ->values()
                    ->all();

                $items[] = [
                    'key'        => $key,
                    'group'      => 'webpage',
                    'label'      => $item['label'],
                    'done'       => $products->isNotEmpty() && $missingIn === [],
                    'missing_in' => $missingIn,
                ];
            }
        }

        $missing = collect($items)->where('done', false)->pluck('label')->values()->all();

        return [
            'complete' => count($items) - count($missing),
            'total'    => count($items),
            'items'    => $items,
            'missing'  => $missing,
        ];
    }

    /**
     * @return Collection<int, Product>|null
     */
    private function productsInOpenShops(Model $row): ?Collection
    {
        if (!$row instanceof MasterAsset || !$row->relationLoaded('products')) {
            return null;
        }

        if ($row->products->isNotEmpty() && !$row->products->first()->relationLoaded('webpage')) {
            return null;
        }

        return $row->products->filter(
            fn (Product $product) => $product->shop && $product->shop->state != ShopStateEnum::CLOSED
        )->values();
    }
}
