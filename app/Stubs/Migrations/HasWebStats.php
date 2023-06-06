<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasWebStats
{
    public function webStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_webpages')->default(0);


        return $table;
    }
}
