<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:41:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Models\Dropshipping\CustomerClient;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchCustomerClients extends FetchAction
{
    public string $commandSignature = 'fetch:customer-clients {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?CustomerClient
    {
        if ($customerClientData = $organisationSource->fetchCustomerClient($organisationSourceId)) {
            if ($customerClient = CustomerClient::withTrashed()->where('source_id', $customerClientData['customer_client']['source_id'])
                ->first()) {
                $customerClient = UpdateCustomerClient::run(
                    customerClient: $customerClient,
                    modelData:      $customerClientData['customer_client']
                );

                if ($deliveryAddress = $customerClient->getAddress('delivery')) {
                    UpdateAddress::run($deliveryAddress, $customerClientData['delivery_address']);
                } else {
                    StoreAddressAttachToModel::run($customerClient, $customerClientData['delivery_address'], ['scope' => 'delivery']);
                }
            } else {
                $customerClient = StoreCustomerClient::run(
                    customer:      $customerClientData['customer'],
                    modelData:     $customerClientData['customer_client'],
                    addressesData: $customerClientData['delivery_address']
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
            $query->where('Customer Client Store Key', $this->shop->source_id);
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
            $query->where('Customer Client Store Key', $this->shop->source_id);
        }

        return $query->count();
    }
}
