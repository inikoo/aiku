<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:58:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits\Stubs;

use App\Enums\DateIntervals\PeriodsEnum;
use App\Enums\DateIntervals\PreviousQuartersEnum;
use App\Enums\DateIntervals\PreviousYearsEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasDateIntervalsStats
{
    public function dateIntervals(Blueprint $table, string $subject=''): Blueprint
    {
        $subject=$subject ? $subject.'_' : '';

        foreach (PeriodsEnum::values() as $col) {
            $table->decimal($subject.$col)->default(0);
        }
        foreach (PeriodsEnum::lastYearValues() as $col) {
            $table->decimal($subject.$col.'_ly')->default(0);
        }
        foreach (PreviousYearsEnum::values() as $col) {
            $table->decimal($subject.$col)->default(0);
        }
        foreach (PreviousQuartersEnum::values() as $col) {
            $table->decimal($subject.$col)->default(0);
        }

        return $table;
    }
}
