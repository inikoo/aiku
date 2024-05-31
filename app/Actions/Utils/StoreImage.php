<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:50:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Utils;

use App\Actions\Studio\Media\UpdateIsAnimatedMedia;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\Goods\TradeUnit;
use App\Models\HumanResources\Employee;
use App\Models\Catalogue\Product;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Guest;

class StoreImage
{
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(
        Employee|Guest|Product|Stock|TradeUnit|Customer|SupplierProduct|Supplier|Agent $subject,
        string $imagePath,
        string $filename,
        string $collection='photo'
    ): Employee|Guest|Product|Stock|TradeUnit|Customer|SupplierProduct|Supplier|Agent {
        $checksum = md5_file($imagePath);

        $media = $subject->media()->where('collection_name', $collection)->where('checksum', $checksum)->first();

        if (!$media) {
            $media=$subject->addMedia($imagePath)
                ->preservingOriginal()
                ->withProperties(
                    [
                        'checksum'    => $checksum,
                        'group_id'    => $subject->getGroupId()
                    ]
                )
                ->usingName($filename)
                ->usingFileName(dechex(crc32($checksum)).".".pathinfo($imagePath, PATHINFO_EXTENSION))
                ->toMediaCollection($collection);
            $media->refresh();
            UpdateIsAnimatedMedia::run($media, $imagePath);
        }
        return $subject;
    }
}
