<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 04 Nov 2020 12:03:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Models\CRM\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Legacy\Traits\LegacyHelpers;


class OrderController extends Controller {
    use LegacyHelpers;

    private $tax_number_validation;
    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Customer::disableAuditing();
    }

    function sync(Request $request) {



    }


    function update($legacy_id, Request $request) {



    }



}
