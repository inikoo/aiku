<?php

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\SupplyChain\SupplierProduct\UpdateSupplierProduct;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgSupplierProductAgentDetails extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;

    private const NULLABLE_NUMERIC_FIELDS = [
        'minimum_carton_order',
        'estimated_lead_time_days',
        'units_per_pack',
        'units_per_carton',
        'cbm',
    ];

    public function handle(OrgSupplierProduct $orgSupplierProduct, array $modelData): SupplierProduct
    {
        return UpdateSupplierProduct::make()->action($orgSupplierProduct->supplierProduct, $modelData);
    }

    public function rules(): array
    {
        return [
            'minimum_carton_order'     => ['sometimes', 'nullable', 'integer', 'min:1'],
            'estimated_lead_time_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
            'units_per_pack'           => ['sometimes', 'nullable', 'integer', 'min:1'],
            'units_per_carton'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'cbm'                      => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        foreach (self::NULLABLE_NUMERIC_FIELDS as $field) {
            if ($this->has($field) && $this->get($field) === '') {
                $this->set($field, null);
            }
        }
    }

    public function asController(Organisation $organisation, OrgSupplierProduct $orgSupplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($organisation, $request);
        abort_unless($this->organisationAgentSupplies($orgSupplierProduct), 404);

        return $this->handle($orgSupplierProduct, $this->validatedData);
    }
}
