<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Jan 2025 14:56:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

/** @noinspection DuplicatedCode */

namespace App\Actions\Maintenance\SysAdmin;

use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Procurement\OrgPartner\StoreOrgPartner;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class AddMissingStatsToDigitalAgencyOrganisation
{
    use AsAction;

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $organisations = Organisation::where('type', OrganisationTypeEnum::DIGITAL_AGENCY)->get();

        /** @var Organisation $organisation */
        foreach ($organisations as $organisation) {
            DB::transaction(function () use ($organisation) {
                $organisation->crmStats()->create();
                $organisation->catalogueStats()->create();
                $organisation->discountsStats()->create();
                $organisation->mailshotsIntervals()->create();

                $paymentServiceProvider = PaymentServiceProvider::where('type', PaymentServiceProviderTypeEnum::ACCOUNT)->first();

                StoreOrgPaymentServiceProvider::make()->action(
                    $paymentServiceProvider,
                    organisation: $organisation,
                    modelData: [
                        'code' => 'account-'.$organisation->code,
                    ]
                );

                $otherOrganisations = Organisation::where('type', OrganisationTypeEnum::SHOP)->where('group_id', $organisation->group->id)->where('id', '!=', $organisation->id)->get();
                foreach ($otherOrganisations as $otherOrganisation) {
                    StoreOrgPartner::make()->action($otherOrganisation, $organisation);
                    StoreOrgPartner::make()->action($organisation, $otherOrganisation);
                }
            });
        }
    }

    public function getCommandSignature(): string
    {
        return 'maintenance:add_missing_stats_to_digital_agency_organisation';
    }

    public function asCommand(Command $command): int
    {
        try {
            $this->handle();
        } catch (Throwable $e) {
            $command->error($e->getMessage());

            return 1;
        }

        return 0;
    }

}
