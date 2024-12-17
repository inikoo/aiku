<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 17-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Comms\OrgPostRoom\UI;

use App\Http\Resources\Catalogue\ProductResource;
use App\Http\Resources\Mail\OrgPostRoomResource;
use App\Models\Catalogue\Product;
use App\Models\Comms\OrgPostRoom;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgPostRoomShowcase
{
    use AsObject;

    public function handle(OrgPostRoom $orgPostRoom): array
    {
        return [
            // 'imagesUploadedRoutes' => [
            //     'name'       => 'grp.org.shops.show.catalogue.products.all_products.images',
            //     'parameters' => [
            //         'organisation' => $product->organisation->slug,
            //         'shop'         => $product->shop->slug,
            //         'product'      => $product->slug
            //     ]
            // ],
            // 'stockImagesRoute' => [
            //     'name'  => 'grp.gallery.stock-images.index',
            //     'parameters'    => []
            // ],
            // 'uploadImageRoute' => [
            //     'name'       => 'grp.models.org.product.images.store',
            //     'parameters' => [
            //         'organisation' => $product->organisation_id,
            //         'product'      => $product->id
            //     ]
            // ],
            // 'attachImageRoute' => [
            //     'name'       => 'grp.models.org.product.images.attach',
            //     'parameters' => [
            //         'organisation' => $product->organisation_id,
            //         'product'      => $product->id
            //     ]
            // ],
            // 'deleteImageRoute' => [
            //     'name'       => 'grp.models.org.product.images.delete',
            //     'parameters' => [
            //         'organisation' => $product->organisation_id,
            //         'product'      => $product->id
            //     ]
            // ],
            // 'product' => ProductResource::make($product),
            'orgPostRoom' => OrgPostRoomResource::make($orgPostRoom),
            'stats'   => $orgPostRoom->stats
        ];
    }
}
