<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 11 Aug 2022 18:07:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */
namespace App\Registries;

use App\Interfaces\ErpGateway;
use Exception;

class ErpGatewayRegistry {

    protected array $gateways = [];

    function register ($name, ErpGateway $instance): static
    {
        $this->gateways[$name] = $instance;
        return $this;
    }

    /**
     * @throws \Exception
     */
    function get($name) {
        if (in_array($name, $this->gateways)) {
            return $this->gateways[$name];
        } else {
            throw new Exception("Invalid gateway");
        }
    }

}
