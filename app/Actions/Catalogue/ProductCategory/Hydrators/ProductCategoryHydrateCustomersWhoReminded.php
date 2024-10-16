<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-11h-40m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Product;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryHydrateCustomersWhoReminded
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
        $categories = ['department', 'subDepartment', 'family'];

        foreach ($categories as $categoryType) {
            $productCategory = $product->{$categoryType};

            if ($productCategory) {
                $methodName = lcfirst($categoryType) . 'BackInStockReminders';

                if (method_exists($productCategory, $methodName)) {
                    $stats = [
                        'number_customers_who_reminded' => $productCategory->{$methodName}()->whereNull('unreminded_at')->count(),
                        'number_customers_who_un_reminded' => $productCategory->{$methodName}()->whereNotNull('unreminded_at')->count(),
                    ];

                    $productCategory->stats->update($stats);
                }
            }
        }
    }

}
