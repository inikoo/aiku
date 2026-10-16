<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-12h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignImages;
use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignRounds;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\Goods\ProductDevelopment;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The Design tab: the latest version of each round, read-only, each opening the design page.
 */
class GetProductDevelopmentDesignOverview
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(ProductDevelopment $productDevelopment): array
    {
        return [
            'rounds' => collect(ProductDevelopmentDesignRoundEnum::cases())->map(function (ProductDevelopmentDesignRoundEnum $round) use ($productDevelopment) {
                $skus = $round->isPerSku()
                    ? $productDevelopment->items()->with('tradeUnit')->orderBy('id')->get()->map(function ($item) use ($productDevelopment, $round) {
                        $latest = ProductDevelopmentDesignRounds::latest($productDevelopment, $round, $item->id);

                        return [
                            'id'     => $item->id,
                            'code'   => $item->tradeUnit->code,
                            'status' => $latest ? ['label' => ProductDevelopmentDesignRoundStatusEnum::labels()[$latest->status->value], 'color' => $latest->status->color()] : null,
                            'latest' => $latest,
                        ];
                    })
                    : collect();
                $latest = $round->isPerSku()
                    ? $skus->pluck('latest')->filter()->sortByDesc('updated_at')->first()
                    : ProductDevelopmentDesignRounds::latest($productDevelopment, $round);

                return [
                    'value'    => $round->value,
                    'label'    => ProductDevelopmentDesignRoundEnum::labels()[$round->value],
                    'icon'     => $round->icon(),
                    'unlocked' => ProductDevelopmentDesignRounds::isUnlocked($productDevelopment, $round),
                    'skipped'  => ProductDevelopmentDesignRounds::isSkipped($productDevelopment, $round),
                    'latest'   => $latest ? [
                        'version' => $latest->version,
                        'status'  => [
                            'value' => $latest->status->value,
                            'label' => ProductDevelopmentDesignRoundStatusEnum::labels()[$latest->status->value],
                            'color' => $latest->status->color(),
                            'icon'  => $latest->status->icon(),
                        ],
                        ...ShowProductDevelopmentDesign::content($latest),
                    ] : null,
                    'progress' => $round->isPerSku() ? ProductDevelopmentDesignRounds::skuProgress($productDevelopment, $round) : null,
                    'skus'     => $skus->map(fn (array $sku) => [...collect($sku)->except('latest')->all(), 'version' => $sku['latest']?->version])->values()->all(),
                    'route'    => ['name' => 'grp.product_developments.design', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'round' => $round->value]],
                ];
            })->all(),
            'images' => ProductDevelopmentDesignImages::forDevelopment($productDevelopment),
        ];
    }
}
