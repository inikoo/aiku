<?php

/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-09h-12m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Product;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateCustomersWhoFavourited
{
    use AsAction;
    use WithEnumStats;
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->product->id))->dontRelease()];
    }
    public function handle(Product $product): void
    {

        $stats         = [
            'number_customers_who_favourited' => $product->favourites()->whereNull('unfavourited_at')->count(),
            'number_customers_who_un_favourited' => $product->favourites()->whereNotNull('unfavourited_at')->count()
        ];

        $product->stats->update($stats);
    }

}
