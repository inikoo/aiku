<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-21h-55m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Json;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\ActionRequest;

class SearchProductDevelopmentSupplierProducts extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const int LIMIT = 20;

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function handle(ProductDevelopment $productDevelopment, string $query): array
    {
        if (!$productDevelopment->supplier_id) {
            return [];
        }

        return SupplierProduct::query()
            ->where('supplier_id', $productDevelopment->supplier_id)
            ->when($query !== '', fn (Builder $builder) => $builder->where(fn (Builder $builder) => $builder->whereStartWith('code', $query)->orWhereAnyWordStartWith('name', $query)))
            ->orderBy('code')
            ->limit(self::LIMIT)
            ->get(['id', 'code', 'name'])
            ->map(fn (SupplierProduct $supplierProduct) => ['value' => $supplierProduct->id, 'label' => trim($supplierProduct->code.' · '.$supplierProduct->name, ' ·')])
            ->all();
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): array
    {
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, trim((string) ($request->input('filter.global') ?? $request->query('q', ''))));
    }
}
