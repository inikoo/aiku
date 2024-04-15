<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 20:10:35 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePaymentServiceProviders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgPaymentServiceProviders;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgPaymentServiceProvider extends OrgAction
{
    public function handle(PaymentServiceProvider $paymentServiceProvider, Organisation $organisation, array $modelData): OrgPaymentServiceProvider
    {

        $modelData=array_merge(
            $modelData,
            [
                'group_id'                   => $organisation->group_id,
                'organisation_id'            => $organisation->id,
                'payment_service_provider_id'=> $paymentServiceProvider->id,
                'type'                       => $paymentServiceProvider->type,
            ]
        );

        /** @var OrgPaymentServiceProvider $orgPaymentServiceProvider */
        $orgPaymentServiceProvider = $paymentServiceProvider->orgPaymentServiceProviders()->create($modelData);
        $orgPaymentServiceProvider->stats()->create();
        OrganisationHydrateOrgPaymentServiceProviders::dispatch($organisation);
        GroupHydratePaymentServiceProviders::dispatch($organisation->group);

        return $orgPaymentServiceProvider;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'payment_service_providers',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'source_id' => ['sometimes', 'string'],
        ];
    }

    public function action(PaymentServiceProvider $paymentServiceProvider, Organisation $organisation, array $modelData): OrgPaymentServiceProvider
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);
        return $this->handle($paymentServiceProvider, $organisation, $this->validatedData);
    }

    public function asController(Organisation $organisation, PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): OrgPaymentServiceProvider
    {
        $this->initialisation($organisation, $request);

        return $this->handle($paymentServiceProvider, $organisation, $this->validatedData);
    }
}
