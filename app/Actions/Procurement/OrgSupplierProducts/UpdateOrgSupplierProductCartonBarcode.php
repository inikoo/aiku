<?php

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Goods\Stock;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgSupplierProductCartonBarcode extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;

    public function handle(Stock $stock, ?string $cartonBarcode): Stock
    {
        $stock->update(['carton_barcode' => blank($cartonBarcode) ? null : trim($cartonBarcode)]);

        return $stock;
    }

    public function rules(): array
    {
        return [
            'carton_barcode' => ['present', 'nullable', 'string', 'max:64', 'regex:/^[\x20-\x7E]+$/'],
        ];
    }

    public function asController(Organisation $organisation, OrgSupplierProduct $orgSupplierProduct, Stock $stock, ActionRequest $request): Stock
    {
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($orgSupplierProduct);

        abort_unless($orgSupplierProduct->supplierProduct->stocks()->where('stocks.id', $stock->id)->exists(), 404);

        return $this->handle($stock, $this->validatedData['carton_barcode']);
    }
}
