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
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgPaymentServiceProvider extends OrgAction
{
    /**
     * @throws \Throwable
     */
    public function handle(PaymentServiceProvider $paymentServiceProvider, Organisation $organisation, array $modelData): OrgPaymentServiceProvider
    {
        $modelData                 = array_merge(
            $modelData,
            [
                'group_id'                    => $organisation->group_id,
                'organisation_id'             => $organisation->id,
                'payment_service_provider_id' => $paymentServiceProvider->id,
                'type'                        => $paymentServiceProvider->type,
            ]
        );
        $orgPaymentServiceProvider = DB::transaction(function () use ($paymentServiceProvider, $modelData, $organisation) {
            /** @var OrgPaymentServiceProvider $orgPaymentServiceProvider */
            $orgPaymentServiceProvider = $paymentServiceProvider->orgPaymentServiceProviders()->create($modelData);
            $orgPaymentServiceProvider->stats()->create();

            return $orgPaymentServiceProvider;
        });
        OrganisationHydrateOrgPaymentServiceProviders::dispatch($organisation)->delay($this->hydratorsDelay);
        GroupHydratePaymentServiceProviders::dispatch($organisation->group)->delay($this->hydratorsDelay);

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
        $rules = [
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
        ];

        if (!$this->strict) {
            $rules['created_at'] = ['sometimes', 'date'];
            $rules['fetched_at'] = ['sometimes', 'date'];
            $rules['source_id']  = ['sometimes', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(PaymentServiceProvider $paymentServiceProvider, Organisation $organisation, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): OrgPaymentServiceProvider
    {
        if (!$audit) {
            OrgPaymentServiceProvider::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($paymentServiceProvider, $organisation, $this->validatedData);
    }

}
