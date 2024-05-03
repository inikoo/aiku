<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 23:29:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasSalesIntervals
{
    use HasDateIntervalsStats;

    public function salesIntervalFields(Blueprint $table, $dateIntervals): Blueprint
    {
        return $this->dateIntervals($table, $dateIntervals);
    }
}
