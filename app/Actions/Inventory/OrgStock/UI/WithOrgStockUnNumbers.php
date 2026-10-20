<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait WithOrgStockUnNumbers
{
    protected function getUnNumbersSubquery(): Builder
    {
        return DB::table('trade_units')
            ->join('model_has_trade_units', function ($join) {
                $join->on('trade_units.id', '=', 'model_has_trade_units.trade_unit_id')
                    ->where('model_has_trade_units.model_type', 'OrgStock');
            })
            ->whereColumn('model_has_trade_units.model_id', 'org_stocks.id')
            ->whereNotNull('trade_units.un_number')
            ->where('trade_units.un_number', '<>', 'None')
            ->selectRaw("jsonb_object_agg(
                trade_units.id,
                jsonb_build_object(
                    'number', trade_units.un_number,
                    'shipping_name', trade_units.proper_shipping_name
                )
            )");
    }
}
