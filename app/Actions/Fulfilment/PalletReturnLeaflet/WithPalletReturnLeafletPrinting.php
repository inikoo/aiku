<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturnLeaflet;

use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

trait WithPalletReturnLeafletPrinting
{
    private PalletReturn $palletReturn;

    public function authorize(ActionRequest $request): bool
    {
        $warehouseId = $this->palletReturn->warehouse_id;

        return $request->user()->authTo([
            "fulfilment-shop.{$this->palletReturn->fulfilment_id}.edit",
            "fulfilment.$warehouseId.edit",
            "supervisor-incoming.$warehouseId",
            "supervisor-fulfilment.$warehouseId",
        ]);
    }

    protected function setPrintNodePrinter(): void
    {
        $user      = request()->user();
        $printerId = $this->leafletPrinterId($user);

        if (!$printerId) {
            throw ValidationException::withMessages([
                'messages' => __('You must set a leaflet printer or a preferred printer in your user settings!'),
            ]);
        }

        if (!Arr::get($user->group->settings, 'printnode.print_by_printnode', false)) {
            throw ValidationException::withMessages([
                'messages' => __('Print by Printnode is not enabled for your group!'),
            ]);
        }

        $this->set('printerId', $printerId);
    }
}
