<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Masters\MasterAsset\UI\GetMasterProductChecklist;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Web\Webpage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * What a shop product and its webpage should carry before it sells well: the master's product
 * information items read from products, its image, the webpage settings, and the checks of the
 * latest site audit of the webpage.
 */
class GetProductChecklist
{
    use AsObject;

    /**
     * @return array<string, array{label: string, filled: string}>
     */
    public function productItems(): array
    {
        return [
            ...GetMasterProductChecklist::make()->items('products', 'Product'),
            'image' => [
                'label'  => __('Image'),
                'filled' => 'products.image_id is not null',
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

    public function whereIncomplete(Builder $query): void
    {
        GetMasterProductChecklist::make()->whereIncomplete($query, 'products', 'Product');
    }

    /**
     * @param array<string, array{label: string, is_passed: ?bool}>|null $auditChecks the checks of the webpage's site audit when already read
     *
     * @return array{complete: int, total: int, items: array<int, array<string, mixed>>, missing: array<int, string>}
     */
    public function handle(Product $product, ?array $auditChecks = null): array
    {
        return $this->forProducts(collect([$product]), $auditChecks !== null && $product->webpage_id ? [$product->webpage_id => $auditChecks] : null)[$product->id];
    }

    /**
     * @param Collection<int, Product> $products
     * @param array<int, array<string, array{label: string, is_passed: ?bool}>>|null $auditChecksByWebpage keyed by webpage id, read here when not given
     *
     * @return array<int, array{complete: int, total: int, items: array<int, array<string, mixed>>, missing: array<int, string>}> keyed by product id
     */
    public function forProducts(Collection $products, ?array $auditChecksByWebpage = null): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        $productItems = $this->productItems();

        $query = Product::query()->whereIn('products.id', $products->pluck('id'))->select('products.id');
        foreach ($productItems as $key => $item) {
            $query->selectRaw("({$item['filled']}) as checklist_$key");
        }
        $rows = $query->get()->keyBy('id');

        (new EloquentCollection($products->all()))->loadMissing('webpage');
        $webpages = $products->pluck('webpage')->filter()->values();

        $auditChecksByWebpage ??= GetProductWebpageAudit::make()->checksForWebpages($webpages);

        return $products->mapWithKeys(function (Product $product) use ($productItems, $rows, $auditChecksByWebpage) {
            $row     = $rows->get($product->id);
            $webpage = $product->webpage;
            $items   = [];

            foreach ($productItems as $key => $item) {
                $items[] = ['key' => $key, 'group' => 'product', 'label' => $item['label'], 'done' => (bool)$row?->getAttribute("checklist_$key")];
            }

            foreach ($this->webpageItems() as $key => $item) {
                $items[] = ['key' => $key, 'group' => 'webpage', 'label' => $item['label'], 'done' => $webpage && ($item['check'])($webpage)];
            }

            $auditChecks = $webpage
                ? $auditChecksByWebpage[$webpage->id] ?? GetProductWebpageAudit::make()->checksForWebpages(collect([$webpage]))[$webpage->id]
                : collect(GetProductWebpageAudit::make()->labels())->map(fn (string $label) => ['label' => $label, 'is_passed' => false])->all();
            foreach ($auditChecks as $key => $check) {
                $items[] = [
                    'key'       => $key,
                    'group'     => 'webpage',
                    'label'     => $check['label'],
                    'done'      => $check['is_passed'] === true,
                    'unchecked' => $check['is_passed'] === null,
                ];
            }

            $missing = collect($items)->where('done', false)->pluck('label')->values()->all();

            return [$product->id => [
                'complete' => count($items) - count($missing),
                'total'    => count($items),
                'items'    => $items,
                'missing'  => $missing,
            ]];
        })->all();
    }
}
