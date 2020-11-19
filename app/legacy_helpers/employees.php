<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:24:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\HR\Employee;


function get_employee_id_from_legacy($employee_legacy_id){

    if(!$employee_legacy_id){
        return null;
    }
    $picker = (new Employee)->firstWhere('legacy_id', $employee_legacy_id);
    if ($picker) {
        return  $picker->id;
    }
    return null;
}

