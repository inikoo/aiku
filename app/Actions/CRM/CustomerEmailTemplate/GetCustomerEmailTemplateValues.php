<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerEmailTemplate;

use App\Models\CRM\Customer;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\User;
use Illuminate\Support\Number;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * What each {placeholder} of a customer email template becomes for this customer, written by
 * this member of staff. The templates keep the placeholders, and they are filled in on sending.
 */
class GetCustomerEmailTemplateValues
{
    use AsAction;

    /**
     * @return array<string, string>
     */
    public function handle(Customer $customer, ?User $user): array
    {
        $basket = $customer->orderInBasket;

        return [
            '{contact_name}' => (string) ($customer->contact_name ?: $customer->name),
            '{company_name}' => (string) ($customer->company_name ?: $customer->name),
            '{shop_name}'    => (string) $customer->shop->name,
            '{website}'      => (string) $customer->shop->website?->getUrl(),
            '{my_name}'      => (string) ($user?->contact_name ?: $user?->username),
            '{basket}'       => $basket ? $basket->transactions()
                ->where('model_type', 'Product')
                ->with('asset')
                ->orderBy('id')
                ->get()
                ->map(fn (Transaction $transaction) => '- '.$transaction->asset?->code.' '.$transaction->asset?->name.' × '.(float) $transaction->quantity_ordered)
                ->implode("\n") : '',
            '{basket_total}' => Number::currency((float) ($basket?->total_amount ?? 0), $customer->shop->currency->code),
        ];
    }
}
