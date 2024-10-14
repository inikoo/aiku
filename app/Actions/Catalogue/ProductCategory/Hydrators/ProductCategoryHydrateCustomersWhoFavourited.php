<?php
/*
 * author Arya Permana - Kirin
 * created on 14-10-2024-09h-12m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryHydrateCustomersWhoFavourited
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
                $methodName = lcfirst($categoryType) . 'Favourites';

                if (method_exists($productCategory, $methodName)) {
                    $stats = [
                        'number_customers_who_favourited' => $productCategory->{$methodName}()->whereNull('unfavourited_at')->count(),
                        'number_customers_who_un_favourited' => $productCategory->{$methodName}()->whereNotNull('unfavourited_at')->count(),
                    ];

                    $productCategory->stats->update($stats);
                }
            }
        }
    }

}
