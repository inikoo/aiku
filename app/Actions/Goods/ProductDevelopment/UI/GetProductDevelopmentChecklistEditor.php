<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-21h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\ProductDevelopmentAccess;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistLevelEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTargetFieldEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentChecklistItem;
use App\Models\Goods\ProductDevelopmentItem;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\User;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentChecklistEditor
{
    use AsAction;

    private ProductDevelopmentAccess $access;

    private User $viewer;

    private bool $canSeeCosts = true;

    private ?string $supplierCurrency = null;

    /**
     * @return array<string, mixed>
     */
    public function handle(ProductDevelopment $productDevelopment, User $viewer, bool $canSeeCosts, ?ProductDevelopmentMemberRoleEnum $onlyRole = null): array
    {
        $this->access      = new ProductDevelopmentAccess();
        $this->viewer      = $viewer;
        $this->canSeeCosts = $canSeeCosts;

        $productDevelopment->loadMissing(['items.tradeUnit', 'items.supplierProduct.currency', 'supplier.currency']);
        $this->supplierCurrency = $productDevelopment->supplier?->currency?->code;
        $checklistItems = $productDevelopment->checklistItems()
            ->with(['completedBy', 'productDevelopment'])
            ->orderBy('id')
            ->get()
            ->when($onlyRole, fn (Collection $rows) => $rows->where('role', $onlyRole))
            ->reject(fn (ProductDevelopmentChecklistItem $row) => !$this->canSeeCosts && ProductDevelopmentTargetFieldEnum::tryFrom((string) $row->target_field)?->isCost());

        $byItem    = $checklistItems->groupBy(fn (ProductDevelopmentChecklistItem $row) => $row->product_development_item_id ?? 0);
        $canLinkSp = $this->access->canEdit($viewer, $productDevelopment)
            || in_array(ProductDevelopmentMemberRoleEnum::AGENT, $this->access->memberRoles($viewer, $productDevelopment), true)
            || $this->access->isAgentOf($viewer, $productDevelopment);

        $needsCountries = $checklistItems->contains(fn (ProductDevelopmentChecklistItem $row) => $row->target_field === ProductDevelopmentTargetFieldEnum::TRADE_UNIT_ORIGIN_COUNTRY->value);

        return [
            'family'    => $byItem->get(0, collect())->where('level', ProductDevelopmentChecklistLevelEnum::FAMILY)->map(fn ($row) => $this->row($row, null))->values()->all(),
            'skus'      => $productDevelopment->items->map(fn (ProductDevelopmentItem $item) => [
                'id'                => $item->id,
                'code'              => $item->tradeUnit->code,
                'name'              => $item->tradeUnit->name,
                'supplier_product'  => $item->supplierProduct ? ['id' => $item->supplierProduct->id, 'code' => $item->supplierProduct->code] : null,
                'can_link_supplier_product' => $canLinkSp && (bool) $productDevelopment->supplier_id,
                'rows'              => $byItem->get($item->id, collect())->map(fn ($row) => $this->row($row, $item))->values()->all(),
            ])->filter(fn (array $sku) => !$onlyRole || count($sku['rows']))->values()->all(),
            'currency'  => $this->supplierCurrency,
            'countries' => $needsCountries ? Country::orderBy('name')->get(['id', 'name'])->map(fn (Country $country) => ['value' => $country->id, 'label' => $country->name])->all() : [],
            'routes'    => [
                'update'           => 'grp.models.product_development.checklist_item.update',
                'supplier_product' => 'grp.models.product_development.item.supplier_product',
                'supplier_products_search' => $productDevelopment->supplier_id ? ['name' => 'grp.product_developments.json.supplier_products', 'parameters' => ['productDevelopment' => $productDevelopment->slug]] : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(ProductDevelopmentChecklistItem $row, ?ProductDevelopmentItem $item): array
    {
        $targetField = ProductDevelopmentTargetFieldEnum::tryFrom((string) $row->target_field);

        return [
            'id'           => $row->id,
            'label'        => $row->label,
            'role'         => [
                'value' => $row->role->value,
                'label' => ProductDevelopmentMemberRoleEnum::labels()[$row->role->value],
                'icon'  => $row->role->icon(),
                'color' => $row->role->color(),
            ],
            'required'     => $row->required,
            'state'        => $row->state->value,
            'input_type'   => $targetField?->inputType() ?? 'textarea',
            'unit'         => $targetField?->isCost() ? null : $targetField?->unit(),
            'currency'     => $targetField?->isCost() ? ($item?->supplierProduct?->currency?->code ?? $this->supplierCurrency) : null,
            'writes_to'    => $targetField?->writesTo(),
            'needs_supplier_product' => $targetField?->isOnSupplierProduct() && !$item?->supplierProduct,
            'value'        => $targetField ? $this->liveValue($targetField, $item) : ($row->value['text'] ?? null),
            'na_reason'    => $row->na_reason,
            'saved_by'     => $row->completedBy?->contact_name ?: $row->completedBy?->username,
            'saved_at'     => $row->completed_at?->toIso8601String(),
            'can_edit'     => $this->access->canEditChecklistItem($this->viewer, $row),
            'can_reopen'   => !$targetField || $row->state->value === 'not_applicable',
        ];
    }

    private function liveValue(ProductDevelopmentTargetFieldEnum $targetField, ?ProductDevelopmentItem $item): mixed
    {
        $model = $targetField->isOnSupplierProduct() ? $item?->supplierProduct : $item?->tradeUnit;

        return $model?->getAttribute($targetField->column());
    }
}
