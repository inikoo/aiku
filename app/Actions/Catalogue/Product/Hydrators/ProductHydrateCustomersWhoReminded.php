<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-11h-39m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Product;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateCustomersWhoReminded
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
            'number_customers_who_reminded' => $product->backInStockReminders()->whereNull('un_reminded_at')->count(),
            'number_customers_who_un_reminded' => $product->backInStockReminders()->whereNotNull('un_reminded_at')->count()
        ];

        $product->stats->update($stats);
    }

}
