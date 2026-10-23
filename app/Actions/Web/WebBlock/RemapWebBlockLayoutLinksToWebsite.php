<?php

/*
 * Created: Thu, 22 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebBlock;

use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;
use stdClass;

class RemapWebBlockLayoutLinksToWebsite
{
    use AsObject;

    private Website $website;

    private int $numberUnmatchedLinks = 0;

    /**
     * @var array<int, Webpage|null>
     */
    private array $counterparts = [];

    /**
     * @return array{layout: mixed, number_unmatched_links: int}
     */
    public function handle(mixed $layout, Website $website): array
    {
        $this->website              = $website;
        $this->numberUnmatchedLinks = 0;
        $this->counterparts         = [];

        $copy = json_decode(json_encode($layout));

        return [
            'layout'                 => $this->walk($copy),
            'number_unmatched_links' => $this->numberUnmatchedLinks,
        ];
    }

    private function walk(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->walk($item), $value);
        }

        if (!$value instanceof stdClass) {
            return $value;
        }

        if ($this->isInternalLink($value)) {
            $this->remapLink($value);
        }

        foreach (get_object_vars($value) as $key => $item) {
            $value->{$key} = $this->walk($item);
        }

        return $value;
    }

    private function isInternalLink(stdClass $value): bool
    {
        return ($value->type ?? null) === 'internal' && is_numeric($value->id ?? null);
    }

    private function remapLink(stdClass $link): void
    {
        $sourceWebpageId = (int) $link->id;

        if (!array_key_exists($sourceWebpageId, $this->counterparts)) {
            $sourceWebpage = Webpage::find($sourceWebpageId);

            if (!$sourceWebpage || $sourceWebpage->website_id === $this->website->id) {
                return;
            }

            $this->counterparts[$sourceWebpageId] = $sourceWebpage->group_id === $this->website->group_id
                ? $this->findCounterpart($sourceWebpage)
                : null;
        }

        $counterpart = $this->counterparts[$sourceWebpageId];

        if (!$counterpart) {
            $this->numberUnmatchedLinks++;

            return;
        }

        $link->id            = $counterpart->id;
        $link->url           = $counterpart->url;
        $link->href          = $counterpart->canonical_url;
        $link->canonical_url = $counterpart->canonical_url;

        if (property_exists($link, 'workshop')) {
            $link->workshop = null;
        }
    }

    private function findCounterpart(Webpage $sourceWebpage): ?Webpage
    {
        if ($sourceWebpage->website?->storefront_id === $sourceWebpage->id) {
            return $this->website->storefront;
        }

        $model = $sourceWebpage->model;

        $counterpartWebpageId = match (true) {
            $model instanceof Product && $model->master_product_id => Product::where('shop_id', $this->website->shop_id)
                ->where('master_product_id', $model->master_product_id)
                ->whereNotNull('webpage_id')
                ->value('webpage_id'),
            $model instanceof ProductCategory && $model->master_product_category_id => ProductCategory::where('shop_id', $this->website->shop_id)
                ->where('master_product_category_id', $model->master_product_category_id)
                ->whereNotNull('webpage_id')
                ->value('webpage_id'),
            $model instanceof Collection && $model->master_collection_id => Collection::where('shop_id', $this->website->shop_id)
                ->where('master_collection_id', $model->master_collection_id)
                ->whereNotNull('webpage_id')
                ->value('webpage_id'),
            default => null,
        };

        if ($counterpartWebpageId) {
            return Webpage::where('website_id', $this->website->id)->find($counterpartWebpageId);
        }

        $sameKindOfWebpage = Webpage::where('website_id', $this->website->id)
            ->where('model_type', $sourceWebpage->model_type);

        return (clone $sameKindOfWebpage)->where('code', $sourceWebpage->code)->first()
            ?? (clone $sameKindOfWebpage)->where('url', $sourceWebpage->url)->first();
    }
}
