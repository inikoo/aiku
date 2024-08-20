<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 07 Dec 2023 14:06:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Events;

use App\Models\Dropshipping\ShopifyUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadProductToShopifyProgressEvent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;


    public ShopifyUser $shopifyUser;
    public int $totalProduct;
    public int $uploadedProduct;

    public function __construct(ShopifyUser $shopifyUser, int $totalProduct, int $uploadedProduct)
    {
        $this->shopifyUser     = $shopifyUser;
        $this->totalProduct    = $totalProduct;
        $this->uploadedProduct = $uploadedProduct;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('shopify.upload-product.' . $this->shopifyUser->id)
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'total_products'    => $this->totalProduct,
            'uploaded_products' => $this->uploadedProduct
        ];
    }

    public function broadcastAs(): string
    {
        return 'action-progress';
    }
}
