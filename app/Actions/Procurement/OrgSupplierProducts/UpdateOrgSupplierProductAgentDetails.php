<?php

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\SupplyChain\SupplierProduct\UpdateSupplierProduct;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Actions\SupplyChain\SupplierProduct\WithPackagingTermsRules;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductAvailabilityStatusEnum;
use App\Enums\SupplyChain\SupplierProduct\SupplierProductUnavailableReasonEnum;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgSupplierProductAgentDetails extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;
    use WithPackagingTermsRules;

    private const NULLABLE_NUMERIC_FIELDS = [
        'minimum_carton_order',
        'estimated_lead_time_days',
        'units_per_pack',
        'units_per_carton',
        'cbm',
        'carton_weight',
        'carton_net_weight',
        'availability_new_cost',
    ];

    private const AVAILABILITY_FIELDS = ['availability_status', 'unavailable_reason', 'available_from', 'availability_new_cost', 'availability_note'];

    public function handle(OrgSupplierProduct $orgSupplierProduct, array $modelData, ?User $user = null): SupplierProduct
    {
        $availabilityBefore = json_encode($orgSupplierProduct->supplierProduct->only(self::AVAILABILITY_FIELDS));
        $supplierProduct    = UpdateSupplierProduct::make()->action($orgSupplierProduct->supplierProduct, $modelData);

        if (json_encode($supplierProduct->refresh()->only(self::AVAILABILITY_FIELDS)) !== $availabilityBefore) {
            NotifyPurchasingOfSupplierProductAvailability::run($supplierProduct, $user);
        }

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'minimum_carton_order'     => ['sometimes', 'nullable', 'integer', 'min:1'],
            'estimated_lead_time_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
            'units_per_pack'           => ['sometimes', 'nullable', 'integer', 'min:1'],
            'units_per_carton'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'cbm'                      => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'carton_dimensions'        => ['sometimes', 'nullable', 'array:l,w,h'],
            'carton_dimensions.l'      => ['required_with:carton_dimensions', 'numeric', 'gt:0'],
            'carton_dimensions.w'      => ['required_with:carton_dimensions', 'numeric', 'gt:0'],
            'carton_dimensions.h'      => ['required_with:carton_dimensions', 'numeric', 'gt:0'],
            'carton_weight'            => ['sometimes', 'nullable', 'integer', 'min:0'],
            'carton_net_weight'        => ['sometimes', 'nullable', 'integer', 'min:0', 'lte:carton_weight'],
            'confirm_lead_time'        => ['sometimes', 'boolean'],
            'availability_status'      => ['sometimes', 'required', Rule::enum(SupplierProductAvailabilityStatusEnum::class)],
            'unavailable_reason'       => ['nullable', 'required_if:availability_status,limited,unavailable', Rule::enum(SupplierProductUnavailableReasonEnum::class)],
            'available_from'           => ['sometimes', 'nullable', 'date'],
            'availability_new_cost'    => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'availability_note'        => ['sometimes', 'nullable', 'string', 'max:2000'],
            ...$this->packagingTermsRules(),
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

        return $this->handle($orgSupplierProduct, $this->validatedData, $request->user());
    }
}
