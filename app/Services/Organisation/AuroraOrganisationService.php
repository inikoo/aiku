<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:46:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation;


use App\Models\Organisations\Organisation;
use App\Services\Organisation\Aurora\FetchAuroraCustomer;
use App\Services\Organisation\Aurora\FetchAuroraCustomerClient;
use App\Services\Organisation\Aurora\FetchAuroraDeliveryNote;
use App\Services\Organisation\Aurora\FetchAuroraEmployee;
use App\Services\Organisation\Aurora\FetchAuroraOrder;
use App\Services\Organisation\Aurora\FetchAuroraShipper;
use App\Services\Organisation\Aurora\FetchAuroraShop;
use App\Services\Organisation\Aurora\FetchAuroraUser;
use Illuminate\Support\Facades\DB;

/**
 * @property \App\Models\Organisations\Organisation $organisation
 */
class AuroraOrganisationService implements SourceOrganisationService
{

    public function initialisation(Organisation $organisation)
    {
        $database_settings = data_get(config('database.connections'), 'aurora');
        data_set($database_settings, 'database', $organisation->data['db_name']);
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');

        $this->organisation = $organisation;
    }

    public function fetchUser($id): ?array
    {
        return (new FetchAuroraUser($this))->fetch($id);
    }

    public function fetchEmployee($id): ?array
    {
        return (new FetchAuroraEmployee($this))->fetch($id);
    }

    public function fetchShop($id): ?array
    {
        return (new FetchAuroraShop($this))->fetch($id);
    }

    public function fetchShipper($id): ?array
    {
        return (new FetchAuroraShipper($this))->fetch($id);
    }

    public function fetchDeliveryNote($id): ?array
    {
        return (new FetchAuroraDeliveryNote($this))->fetch($id);

    }

    public function fetchCustomer($id): ?array
    {
        return (new FetchAuroraCustomer($this))->fetch($id);
    }

    public function fetchOrder($id): ?array
    {

        return (new FetchAuroraOrder($this))->fetch($id);
    }

    public function fetchCustomerClient($id): ?array
    {
        return (new FetchAuroraCustomerClient($this))->fetch($id);
    }
}
