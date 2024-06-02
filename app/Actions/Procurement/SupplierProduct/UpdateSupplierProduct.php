<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 22:27:24 Malaysia Time, Plane Bali - KL
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierProduct;

use App\Actions\GrpAction;
use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Models\SupplyChain\SupplierProduct;
use App\Rules\AlphaDashDotSpaceSlashParenthesis;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;

class UpdateSupplierProduct extends GrpAction
{
    use WithActionUpdate;


    public bool $skipHistoric  = false;


    private SupplierProduct $supplierProduct;

    public function handle(SupplierProduct $supplierProduct, array $modelData, bool $skipHistoric = false): SupplierProduct
    {
        $supplierProduct = $this->update($supplierProduct, $modelData, ['data', 'settings']);
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if (!$skipHistoric and $supplierProduct->wasChanged(
            ['price', 'code', 'name', 'units']
        )) {
            //todo create HistoricSupplierProduct and update current_historic_asset_id if
        }

        SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'max:64',
                new AlphaDashDotSpaceSlashParenthesis(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'supplier_products',
                    extraConditions: [
                        ['column' => 'supplier_id', 'value' => $this->supplierProduct->supplier_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->supplierProduct->id
                        ],
                    ]
                ),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'cost' => ['sometimes', 'required'],
        ];
    }

    public function action(SupplierProduct $supplierProduct, array $modelData, bool $skipHistoric = false, int $hydratorsDelay = 0): SupplierProduct
    {
        $this->asAction        = true;
        $this->hydratorsDelay  = $hydratorsDelay;
        $this->skipHistoric    = $skipHistoric;
        $this->supplierProduct = $supplierProduct;
        $this->initialisation($supplierProduct->group, $modelData);

        return $this->handle($supplierProduct, $this->validatedData);
    }

    public function jsonResponse(SupplierProduct $supplierProduct): SupplierProductResource
    {
        return new SupplierProductResource($supplierProduct);
    }
}
