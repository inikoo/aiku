<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:53:47 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Models\Catalogue\Product;
use Lorisleiva\Actions\Concerns\AsObject;

class GetProductShowcase
{
    use AsObject;

    public function handle(Product $product): array
    {
        return [
            'uploadImageRoute' => [
                'name'       => 'grp.models.org.product.images.store',
                'parameters' => [
                    'organisation' => $product->organisation_id,
                    'product'      => $product->id
                ]
            ]
        ];
    }
}
