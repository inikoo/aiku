<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Refund\UI;

use App\Actions\Accounting\Refund\StoreRefund;
use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class CreateRefund extends OrgAction
{
    private array $referralRoute = [
        'name' => 'dashboard',
        'parameters' => []
    ];

    /**
     * @throws \Throwable
     */
    public function handle(Invoice $invoice): Invoice
    {
        $refund = StoreRefund::make()->action($invoice, []);
        return $refund;

    }

    public function htmlResponse(Invoice $refund, ActionRequest $request): RedirectResponse
    {
        return Redirect::route(
            $this->referralRoute['name'].'.refunds.show',
            array_merge($this->referralRoute['parameters'], [$refund->slug])
        );
    }

    public function rules(): array
    {
        return [
            'referral_route' => ['sometimes','array'],
            'referral_route.name' => ['required','string'],
            'referral_route.parameters' => ['required','array'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Invoice $invoice, ActionRequest $request): Invoice
    {

        $this->initialisationFromShop($invoice->shop, $request);

        if (Arr::has($this->validatedData, 'referral_route')) {
            $this->referralRoute = $this->validatedData['referral_route'];
        }

        return $this->handle($invoice);
    }
}
