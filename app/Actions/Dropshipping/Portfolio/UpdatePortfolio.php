<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:36:21 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\Portfolio;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdatePortfolio extends OrgAction
{
    use WithActionUpdate;


    private Portfolio $dropshippingCustomerPortfolio;

    public function handle(Portfolio $dropshippingCustomerPortfolio, array $modelData): Portfolio
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
            'reference' => ['sometimes', 'nullable','string', 'max:255',
                            new IUnique(
                                table: 'portfolios',
                                extraConditions: [
                                    ['column' => 'customer_id', 'value' => $this->shop->id],
                                    ['column' => 'status', 'value' => true],
                                    ['column' => 'id', 'value' => $this->dropshippingCustomerPortfolio->id, 'operator' => '!='],
                                ]
                            ),
                ],
            'status'          => 'sometimes|boolean',
            'created_at'      => 'sometimes|date',
            'last_added_at'   => 'sometimes|date',
            'last_removed_at' => 'sometimes|date',
            'source_id'       => 'sometimes|string|max:255',
        ];
    }



    public function action(Portfolio $dropshippingCustomerPortfolio, array $modelData): Portfolio
    {
        $this->asAction                      = true;
        $this->dropshippingCustomerPortfolio = $dropshippingCustomerPortfolio;
        $this->initialisationFromShop($dropshippingCustomerPortfolio->shop, $modelData);

        return $this->handle($dropshippingCustomerPortfolio, $this->validatedData);
    }


}