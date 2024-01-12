<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:41:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Models\Dropshipping\CustomerClient;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchCustomerClients extends FetchAction
{
    public string $commandSignature = 'fetch:customer-clients {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?CustomerClient
    {
        if ($customerClientData = $organisationSource->fetchCustomerClient($organisationSourceId)) {
            if ($customerClient = CustomerClient::withTrashed()->where('source_id', $customerClientData['customer_client']['source_id'])
                ->first()) {
                $customerClient = UpdateCustomerClient::make()->asFetch(
                    customerClient: $customerClient,
                    modelData:      $customerClientData['customer_client']
                );

            } else {
                $customerClient = StoreCustomerClient::make()->asFetch(
                    customer:      $customerClientData['customer'],
                    modelData:     $customerClientData['customer_client'],
                );
            }

            return $customerClient;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Customer Client Dimension')
            ->select('Customer Client Key as source_id')
            ->orderBy('source_id');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData=explode(':', $this->shop->source_id);
            $query->where('Customer Client Store Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Customer Client Dimension');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        if ($this->shop) {
            $sourceData=explode(':', $this->shop->source_id);
            $query->where('Customer Client Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Customer Client Dimension')->update(['aiku_id' => null]);
    }

}
