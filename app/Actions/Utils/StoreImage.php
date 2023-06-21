<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:50:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Utils;

use App\Actions\WithActionUpdate;
use App\Models\Auth\Guest;
use App\Models\CRM\Customer;
use App\Models\Goods\TradeUnit;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Stock;
use App\Models\Market\Product;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;

class StoreImage
{
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(
        Employee|Guest|Product|Stock|TradeUnit|Customer|SupplierProduct|Supplier|Agent $subject,
        string $image_path,
        string $filename,
        string $collection='photo'
    ): Employee|Guest|Product|Stock|TradeUnit|Customer|SupplierProduct|Supplier|Agent {
        $checksum = md5_file($image_path);
        if ($subject->getMedia($collection, ['checksum' => $checksum])->count() == 0) {
            $subject->addMedia($image_path)
                ->preservingOriginal()
                ->withCustomProperties(['checksum' => $checksum])
                ->usingName($filename)
                ->usingFileName($checksum.".".pathinfo($image_path, PATHINFO_EXTENSION))
                ->toMediaCollection($collection, 'group');
        }
        return $subject;
    }
}
