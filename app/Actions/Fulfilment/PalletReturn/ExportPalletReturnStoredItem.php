<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Traits\Authorisations\WithFulfilmentShopAuthorisation;
use App\Actions\OrgAction;
use App\Actions\Traits\WithExportData;
use App\Exports\StoredItem\PalletReturnPalletStoredItemExport;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPalletReturnStoredItem extends OrgAction
{
    use WithExportData;
    use WithFulfilmentShopAuthorisation;

    /**
     * @throws \Throwable
     */
    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): BinaryFileResponse
    {
        $type = $modelData['type'];

        return $this->export(new PalletReturnPalletStoredItemExport($fulfilmentCustomer), 'pallet-return-stored-items', $type);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): BinaryFileResponse
    {
        abort_unless($fulfilmentCustomer->fulfilment_id === $fulfilment->id, 404);
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }
}
