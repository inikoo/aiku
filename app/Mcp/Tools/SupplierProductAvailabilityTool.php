<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Actions\Inventory\OrgStock\DiscontinueOrgStocks;
use App\Actions\Procurement\OrgSupplierProducts\UpdateOrgSupplierProduct;
use App\Actions\SupplyChain\SupplierProduct\UpdateSupplierProduct;
use App\Enums\SysAdmin\McpChange\McpChangeTypeEnum;
use App\Models\Procurement\OrgSupplierProduct;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Makes the supplier products of SKOs (organisation stock) unavailable, or available again, so they can no longer be ordered from the supplier. Show org-stock-discontinue-preview-tool first (its supplier_products list who each SKO is bought from) and only call this after the user confirmed in their own words. scope organisation (default) switches it off for this organisation only; scope supplier marks the supplier product itself unavailable for every organisation, for when the supplier no longer makes it. supplier narrows to one supplier code when a SKO has several. A reason is required when making unavailable. Every change is logged with request_text and can be reverted. Only for users enrolled to discontinue SKOs through their assistant.')]
class SupplierProductAvailabilityTool extends AikuOrgStockDiscontinueTool
{
    use WithMcpChangeLog;

    public function handle(Request $request): Response
    {
        $request->validate([
            'organisation' => ['required', 'string'],
            'codes'        => ['required', 'array', 'min:1', 'max:50'],
            'codes.*'      => ['string'],
            'is_available' => ['required', 'boolean'],
            'scope'        => ['sometimes', 'in:organisation,supplier'],
            'supplier'     => ['sometimes', 'nullable', 'string'],
            'reason'       => ['required_if:is_available,false', 'nullable', 'string', 'max:1000'],
            'request_text' => ['required', 'string', 'max:4000'],
        ]);

        $denied = $this->denied($request);
        if ($denied) {
            return $denied;
        }

        if (!DiscontinueOrgStocks::canChangeGroupStatus($request->user())) {
            return Response::error('Changing supplier product availability needs the Supply Chain Manager permission. Do not retry.');
        }

        $organisation = $this->organisationByCode($request);
        if (!$organisation) {
            return $this->organisationNotFoundError($request);
        }

        [$orgStocks, $missing] = $this->orgStocksByCodes($organisation, $request->get('codes'));
        if ($missing) {
            return Response::error('Unknown SKO codes in '.$organisation->code.': '.implode(', ', $missing).'. Nothing was changed.');
        }

        $supplierCode = $request->get('supplier');

        $orgSupplierProducts = OrgSupplierProduct::where('organisation_id', $organisation->id)
            ->whereIn('id', DB::table('org_stock_has_org_supplier_products')->whereIn('org_stock_id', $orgStocks->pluck('id'))->select('org_supplier_product_id'))
            ->when($supplierCode, fn ($query) => $query->whereHas('supplierProduct.supplier', fn ($query) => $query->whereRaw('lower(code) = ?', [strtolower($supplierCode)])))
            ->with('supplierProduct')
            ->get();

        if ($orgSupplierProducts->isEmpty()) {
            return Response::error('No supplier products found for '.$orgStocks->pluck('code')->implode(', ').($supplierCode ? ' from supplier '.$supplierCode : '').'. Nothing was changed.');
        }

        $isAvailable    = $request->boolean('is_available');
        $isSupplierWide = $request->get('scope') === 'supplier';

        $target = [
            'org_supplier_product_ids' => $isSupplierWide ? [] : $orgSupplierProducts->pluck('id')->all(),
            'supplier_product_ids'     => $isSupplierWide ? $orgSupplierProducts->pluck('supplier_product_id')->unique()->values()->all() : [],
        ];

        DB::transaction(fn () => $this->recordChange(
            $request,
            McpChangeTypeEnum::SUPPLIER_PRODUCT_AVAILABILITY,
            'Supplier products of '.$orgStocks->pluck('code')->implode(', ').' ('.$organisation->code.($isSupplierWide ? ', every organisation' : '').') '.($isAvailable ? 'available' : 'unavailable'),
            $target,
            function () use ($orgSupplierProducts, $isAvailable, $isSupplierWide) {
                foreach ($orgSupplierProducts as $orgSupplierProduct) {
                    if ($isSupplierWide) {
                        UpdateSupplierProduct::make()->action($orgSupplierProduct->supplierProduct, ['is_available' => $isAvailable]);
                    } else {
                        UpdateOrgSupplierProduct::make()->action($orgSupplierProduct, ['is_available' => $isAvailable]);
                    }
                }
            },
            ['reason' => $request->get('reason')]
        ));

        return Response::json([
            'organisation'      => $organisation->code,
            'scope'             => $isSupplierWide ? 'supplier' : 'organisation',
            'is_available'      => $isAvailable,
            'supplier_products' => $orgSupplierProducts->map(fn (OrgSupplierProduct $orgSupplierProduct) => $orgSupplierProduct->supplierProduct->code)->unique()->values()->all(),
            'change_log_id'     => $this->mcpChange?->id,
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'organisation' => $schema->string()->description('Organisation code or slug the SKO codes belong to')->required(),
            'codes'        => $schema->array()->items($schema->string())->description('SKO codes, up to 50; every supplier product they are bought through is changed')->required(),
            'is_available' => $schema->boolean()->description('false = can no longer be ordered from the supplier, true = can be ordered again')->required(),
            'scope'        => $schema->string()->description('organisation (default): only this organisation stops ordering it; supplier: the supplier no longer has it, for every organisation'),
            'supplier'     => $schema->string()->description('Supplier code, to change only that supplier\'s product when a SKO has several'),
            'reason'       => $schema->string()->description('Why, in the user\'s words; required when making unavailable'),
            'request_text' => $schema->string()->description('The user\'s request, verbatim, kept in the change log')->required(),
        ];
    }
}
