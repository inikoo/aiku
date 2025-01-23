<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Refund;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Ordering\Order;
use App\Rules\IUnique;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreRefund extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;


    private Order|Customer|RecurringBill $parent;

    public function handle(Customer|Order|RecurringBill $parent, array $modelData): Invoice
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);

        return DB::transaction(function () use ($parent, $modelData) {
            /** @var Invoice $invoice */
            $invoice = $parent->invoices()->create($modelData);
            $invoice->stats()->create();

            return $invoice;
        });
    }

    public function rules(): array
    {
        $rules = [
            'reference'       => [
                'required',
                'max:64',
                'string',
                new IUnique(
                    table: 'invoices',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                    ]
                ),
            ],
            'currency_id'     => ['required', 'exists:currencies,id'],
            'type'            => ['required', Rule::enum(InvoiceTypeEnum::class)],
            'net_amount'      => ['required', 'numeric'],
            'total_amount'    => ['required', 'numeric'],
            'gross_amount'    => ['required', 'numeric'],
            'rental_amount'   => ['sometimes', 'required', 'numeric'],
            'goods_amount'    => ['sometimes', 'required', 'numeric'],
            'services_amount' => ['sometimes', 'required', 'numeric'],
            'tax_amount'      => ['required', 'numeric'],


            'date'             => ['sometimes', 'date'],
            'tax_liability_at' => ['sometimes', 'date'],
            'data'             => ['sometimes', 'array'],
            'sales_channel_id' => [
                'sometimes',
                'required',
                Rule::exists('sales_channels', 'id')->where(function ($query) {
                    $query->where('group_id', $this->shop->group_id);
                })
            ],
        ];

        if (!$this->strict) {
            $rules['reference'] = [
                'required',
                'max:64',
                'string'
            ];

            $rules['tax_category_id'] = ['sometimes', 'required', 'exists:tax_categories,id'];
            $rules['address_id'] = ['required', 'exists:addresses,id'];
            $rules                    = $this->orderingAmountNoStrictFields($rules);
            $rules                    = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function action(Customer|Order|RecurringBill $parent, array $modelData): Invoice
    {
        $this->strict = false;
        $this->initialisationFromShop($parent->shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }
}
