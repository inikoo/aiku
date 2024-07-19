<?php

namespace App\Actions\Web\Website;

use App\Http\Resources\Catalogue\ProductResource;
use App\Models\Catalogue\Product;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopProduct
{
    use AsObject;

    public function handle(Website $website,Product $product): array
    {
        return [
            'product' => ProductResource::make($product)

        ];
    }
}
