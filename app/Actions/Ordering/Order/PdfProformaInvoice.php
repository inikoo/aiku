<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Oct 2025 11:36:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Accounting\Invoice\WithInvoicesExport;
use App\Actions\OrgAction;
use App\Actions\Traits\WithExportData;
use App\Actions\Traits\WithProformaInvoicePdf;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class PdfProformaInvoice extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithInvoicesExport;
    use WithExportData;
    use WithProformaInvoicePdf {
        WithProformaInvoicePdf::rules insteadof WithExportData;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo([
            "orders.{$this->shop->id}.view",
            "orders.{$this->shop->id}.edit",
            "accounting.{$this->organisation->id}.view",
        ]);
    }

    public function asController(Organisation $organisation, Shop $shop, Order $order, ActionRequest $request): Response
    {
        abort_unless($order->shop_id === $shop->id, 404);
        $this->initialisationFromShop($shop, $request);

        return $this->handle($order, $this->validatedData);
    }
}
