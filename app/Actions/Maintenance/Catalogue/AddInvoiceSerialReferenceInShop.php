<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Feb 2025 02:00:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Catalogue;

use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class AddInvoiceSerialReferenceInShop
{
    use AsAction;

    public function handle(): void
    {
        Shop::all()->each(function ($shop) {
            $shop->serialReferences()->create(
                [
                    'model'           => SerialReferenceModelEnum::INVOICE,
                    'organisation_id' => $shop->organisation->id,
                    'format'          => 'inv-'.$shop->slug.'-%04d'
                ]
            );
        });
    }



    public function getCommandSignature(): string
    {
        return 'maintenance:add_invoice_serial_reference_in_shop';
    }

    public function asCommand(Command $command): int
    {
        try {
            $this->handle();
        } catch (Throwable $e) {
            $command->error($e->getMessage());

            return 1;
        }

        return 0;
    }

}
