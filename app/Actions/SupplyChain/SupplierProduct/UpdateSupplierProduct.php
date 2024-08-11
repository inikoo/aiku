<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 14:46:44 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct;

use App\Actions\GrpAction;
use App\Actions\SupplyChain\SupplierProduct\Search\SupplierProductRecordSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SupplyChain\SupplierProductResource;
use App\Models\SupplyChain\SupplierProduct;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
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

        SupplierProductRecordSearch::dispatch($supplierProduct);

        return $supplierProduct;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'max:64',
                new AlphaDashDotSpaceSlashParenthesisPlus(),
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
