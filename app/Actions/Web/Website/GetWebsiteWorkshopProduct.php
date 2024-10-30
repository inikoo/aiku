<?php

namespace App\Actions\Web\Website;

use App\Http\Resources\Catalogue\ProductWebpageResource;
use App\Models\Catalogue\Product;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopProduct
{
    use AsObject;

    public function handle(Website $website, Product $product): array
    {
        // 'product' => ProductWebpageResource::make($product),
        return [
            'settings' => $website->settings,
            'updateProductTemplate' => [
                    'name'       => 'grp.models.website.settings.update',
                    'parameters' => [
                        'website' => $website->id
                    ]
                ]
        ];
    }
}
