<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Traits\Authorisations\WithFulfilmentShopAuthorisation;
use App\Actions\Accounting\Invoice\WithPalletExport;
use App\Actions\OrgAction;
use App\Actions\Traits\WithExportData;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class PdfPallet extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithExportData;
    use WithPalletExport;
    use WithFulfilmentShopAuthorisation;

    public function handle(Pallet $pallet): Response
    {
        return $this->processDataExportPdf($pallet);
    }

    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Pallet $pallet, ActionRequest $request): Response
    {
        abort_unless($fulfilmentCustomer->fulfilment_id === $fulfilment->id && $pallet->fulfilment_customer_id === $fulfilmentCustomer->id, 404);
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($pallet);
    }
}
