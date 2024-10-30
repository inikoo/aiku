<?php

namespace App\Actions\Web\Website;

use App\Models\Catalogue\Product;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopProduct
{
    use AsObject;

    public function handle(Website $website, Product $product): array
    {
        $propsValue = [
            'settings' => $website->settings,
        ];
        $updateRoute = [
            'updateRoute' => [
                'name'       => 'grp.models.website.settings.update',
                'parameters' => [
                    'website' => $website->id
                ]
            ]
                ];

        return array_merge($propsValue, $updateRoute);
    }
}
