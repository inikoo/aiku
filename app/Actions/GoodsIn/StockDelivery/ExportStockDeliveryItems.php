<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithStockDeliveryDocumentAuthorisation;
use App\Exports\GoodsIn\StockDeliveryItemsExport;
use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The lines of a container as CSV, the same as its PDF prints them.
 */
class ExportStockDeliveryItems extends OrgAction
{
    use WithStockDeliveryDocumentAuthorisation;
    use WithAgentOrganisation;

    public function handle(StockDelivery $stockDelivery): BinaryFileResponse
    {
        return Excel::download(
            new StockDeliveryItemsExport($stockDelivery),
            preg_replace('/[^A-Za-z0-9._-]/', '-', $stockDelivery->reference).'.csv',
            ExcelWriter::CSV
        );
    }

    public function asController(Organisation $organisation, StockDelivery $stockDelivery, ActionRequest $request): BinaryFileResponse
    {
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($stockDelivery);

        return $this->handle($stockDelivery);
    }
}
