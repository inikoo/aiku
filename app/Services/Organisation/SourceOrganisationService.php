<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:44:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation;


use App\Models\Organisations\Organisation;

interface SourceOrganisationService
{
    public function fetchUser($id);

    public function fetchEmployee($id);

    public function fetchShop($id);

    public function fetchShipper($id);

    public function fetchDeliveryNote($id);

    public function fetchCustomer($id);

    public function fetchCustomerClient($id);

    public function fetchOrder($id);

    public function initialisation(Organisation $organisation);
}

