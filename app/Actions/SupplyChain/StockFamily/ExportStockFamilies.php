<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily;

use App\Actions\Traits\WithExportData;
use App\Exports\Inventory\StockFamiliesExport;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportStockFamilies
{
    use AsAction;
    use WithAttributes;
    use WithExportData;

    /**
     * @throws \Throwable
     */
    public function handle(array $modelData): BinaryFileResponse
    {
        $type = $modelData['type'];

        return $this->export(new StockFamiliesExport(), 'stock-families', $type);
    }

    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): BinaryFileResponse
    {
        $this->setRawAttributes($request->all());
        $this->validateAttributes();

        return $this->handle($request->all());
    }
}
