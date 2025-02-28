<?php

/*
 * author Arya Permana - Kirin
 * created on 28-02-2025-08h-36m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\Pallet;

use App\Exports\Pallets\PalletStoredItemTemplateExport;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadPalletStoredItemTemplate
{
    use AsAction;
    use WithAttributes;

    public function handle(): BinaryFileResponse
    {

        return Excel::download(new PalletStoredItemTemplateExport(), 'pallet_stored_items_template.xlsx');
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery): BinaryFileResponse
    {
        return $this->handle();
    }
}
