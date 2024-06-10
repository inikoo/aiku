<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:36:21 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\DropshippingCustomerPortfolio;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshippingx\DropshippingCustomerPortfolio;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateDropshippingCustomerPortfolio extends OrgAction
{
    use WithActionUpdate;


    private DropshippingCustomerPortfolio $dropshippingCustomerPortfolio;

    public function handle(DropshippingCustomerPortfolio $dropshippingCustomerPortfolio, array $modelData): DropshippingCustomerPortfolio
    {
        $dropshippingCustomerPortfolio = $this->update($dropshippingCustomerPortfolio, $modelData, ['data']);

        if ($dropshippingCustomerPortfolio->wasChanged(['status'])) {
            // put here the hydrators
        }


        return $dropshippingCustomerPortfolio;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'reference' => ['sometimes','string', 'max:255',
                            new IUnique(
                                table: 'dropshipping_customer_portfolios',
                                extraConditions: [
                                    ['column' => 'customer_id', 'value' => $this->shop->id],
                                    ['column' => 'status', 'value' => true],
                                    ['column' => 'id', 'value' => $this->dropshippingCustomerPortfolio->id, 'operator' => '!='],
                                ]
                            ),
                ],
        ];
    }



    public function action(DropshippingCustomerPortfolio $dropshippingCustomerPortfolio, array $modelData): DropshippingCustomerPortfolio
    {
        $this->asAction                      = true;
        $this->dropshippingCustomerPortfolio = $dropshippingCustomerPortfolio;
        $this->initialisationFromShop($dropshippingCustomerPortfolio->shop, $modelData);

        return $this->handle($dropshippingCustomerPortfolio, $this->validatedData);
    }


}
