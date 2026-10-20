<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-10h-45m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Links purchase orders to a development by hand, or removes such links. POs that order the
 * development's supplier products are linked on their own and need nothing here.
 */
class LinkProductDevelopmentPurchaseOrder extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const string LINK = 'link';
    public const string UNLINK = 'unlink';

    private ProductDevelopment $productDevelopment;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopment');

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canEdit($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopment
    {
        $purchaseOrders = PurchaseOrder::whereIn('id', $modelData['purchase_order_ids'])->orderBy('reference')->get();
        $link           = $modelData['operation'] === self::LINK;

        if ($link) {
            $productDevelopment->purchaseOrders()->syncWithoutDetaching($purchaseOrders->pluck('id')->all());
        } else {
            $productDevelopment->purchaseOrders()->detach($purchaseOrders->pluck('id')->all());
        }

        foreach ($purchaseOrders as $purchaseOrder) {
            RecordProductDevelopmentActivity::record(
                $productDevelopment,
                $link ? RecordProductDevelopmentActivity::PURCHASE_ORDER_LINKED : RecordProductDevelopmentActivity::PURCHASE_ORDER_UNLINKED,
                [],
                ['purchase_order' => $purchaseOrder->reference]
            );
        }

        return $productDevelopment;
    }

    public function rules(): array
    {
        return [
            'purchase_order_ids'   => ['required', 'array', 'min:1'],
            'purchase_order_ids.*' => ['integer', 'distinct', Rule::exists('purchase_orders', 'id')->where('group_id', $this->productDevelopment->group_id)],
            'operation'            => ['required', Rule::in([self::LINK, self::UNLINK])],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $supplierId = $this->productDevelopment->supplier_id;
        if ($this->get('operation') !== self::LINK || !$supplierId) {
            return;
        }

        $otherSupplier = PurchaseOrder::whereIn('id', (array) $this->get('purchase_order_ids'))
            ->whereNotNull('supplier_id')
            ->where('supplier_id', '!=', $supplierId)
            ->pluck('reference');
        if ($otherSupplier->isNotEmpty()) {
            $validator->errors()->add('purchase_order_ids', trans_choice('This purchase order is for another supplier: :references|These purchase orders are for another supplier: :references', $otherSupplier->count(), ['references' => $otherSupplier->implode(', ')]));
        }
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopment
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user(), $this->validatedData);
    }

    public function action(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopment
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $user, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
