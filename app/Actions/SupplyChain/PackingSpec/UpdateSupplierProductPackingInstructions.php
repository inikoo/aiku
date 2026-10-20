<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

/**
 * The steps the packers follow for a supplier product, in order (check the goods, label them, wrap them, add the
 * desiccant, pack in the special box), printed on the packing instructions of each purchase order. The agent may give
 * other products of the same supplier the same steps.
 */
class UpdateSupplierProductPackingInstructions extends OrgAction
{
    use WithAgentOrganisation;
    use WithPackingCopyTargets;
    use WithProcurementEditAuthorisation;

    public const int MAX_STEPS = 30;

    /**
     * @param list<string> $instructions
     */
    public function handle(SupplierProduct $supplierProduct, array $instructions): SupplierProduct
    {
        $steps = array_values(array_filter(array_map(fn ($step) => trim((string)$step), $instructions), fn (string $step) => $step !== ''));

        $supplierProduct->update(['packing_instructions' => $steps ?: null]);

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'instructions'   => ['present', 'array', 'max:'.self::MAX_STEPS],
            'instructions.*' => ['nullable', 'string', 'max:255'],
            'copy_to'        => ['sometimes', 'array'],
            'copy_to.*'      => ['integer'],
        ];
    }

    public function asController(Organisation $organisation, OrgSupplierProduct $orgSupplierProduct, ActionRequest $request): RedirectResponse
    {
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($orgSupplierProduct);

        $instructions = $this->validatedData['instructions'];
        $this->handle($orgSupplierProduct->supplierProduct, $instructions);

        $this->copyTargets($orgSupplierProduct, Arr::get($this->validatedData, 'copy_to', []))
            ->each(fn (OrgSupplierProduct $target) => $this->handle($target->supplierProduct, $instructions));

        return redirect()->back();
    }
}
