<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 22:11:19 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Accounting\OrgPaymentServiceProviderResource;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrgPaymentServiceProvider extends GrpAction
{
    use WithActionUpdate;

    private orgPaymentServiceProvider $orgPaymentServiceProvider;

    public function handle(OrgPaymentServiceProvider $orgPaymentServiceProvider, array $modelData): OrgPaymentServiceProvider
    {
        return $this->update($orgPaymentServiceProvider, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'between:2,16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_service_providers',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->orgPaymentServiceProvider->id
                        ],
                    ]
                ),
            ],
            'name'      => [
                'sometimes',
                'required',
                'max:255',
                'string',
            ],
        ];
    }

    public function asController(OrgPaymentServiceProvider $orgPaymentServiceProvider, ActionRequest $request): OrgPaymentServiceProvider
    {
        $this->orgPaymentServiceProvider = $orgPaymentServiceProvider;
        $this->initialisation($orgPaymentServiceProvider->group, $request);

        return $this->handle($orgPaymentServiceProvider, $this->validatedData);
    }

    public function action(OrgPaymentServiceProvider $orgPaymentServiceProvider, $modelData): OrgPaymentServiceProvider
    {
        $this->asAction                  = true;
        $this->orgPaymentServiceProvider = $orgPaymentServiceProvider;
        $this->initialisation($orgPaymentServiceProvider->group, $modelData);

        return $this->handle($orgPaymentServiceProvider, $this->validatedData);
    }

    public function jsonResponse(OrgPaymentServiceProvider $orgPaymentServiceProvider): OrgPaymentServiceProviderResource
    {
        return new OrgPaymentServiceProviderResource($orgPaymentServiceProvider);
    }
}
