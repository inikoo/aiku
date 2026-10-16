<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Catalogue;

use App\Actions\Catalogue\ProductCategory\UI\GetFamilyPageScore;
use App\Models\Catalogue\ProductCategory;

/**
 * A shop family of a master family, with where its webpage is in the SEO workflow.
 *
 * ponytail: runs GetFamilyPageScore per row (about ten queries). A master family has one family
 * per shop, so the page stays small; batch the page score queries if master shops grow past a few dozen shops.
 */
class FamiliesInMasterFamilyResource extends FamiliesResource
{
    public function toArray($request): array
    {
        /** @var ProductCategory $family */
        $family    = $this->resource;
        $pageScore = GetFamilyPageScore::run($family);
        $steps     = collect($pageScore['steps'] ?? GetFamilyPageScore::make()->steps(false, false, false, false))
            ->map(fn (array $step) => ['key' => $step['key'], 'label' => $step['label'], 'done' => $step['is_done']]);
        $current   = $steps->search(fn (array $step) => !$step['done']);

        return [
            ...parent::toArray($request),
            'shop_status' => [
                'state' => $family->state->value,
                'label' => $family->state->labels()[$family->state->value],
            ],
            'workflow'    => [
                'steps'   => $steps->all(),
                'current' => ($current === false ? $steps->count() - 1 : $current) + 1,
            ],
            'page_score'  => $pageScore['score'] ?? null,
            'public_url'  => $family->webpage?->canonical_url,
        ];
    }
}
