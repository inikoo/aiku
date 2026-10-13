<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\OrgStock;

use App\Enums\EnumHelperTrait;

enum OrgStockDiscontinueSuggestionReasonEnum: string
{
    use EnumHelperTrait;

    case HIGH_COMPLAINTS = 'high_complaints';
    case SLOW_SELLER = 'slow_seller';
    case HIGH_COMPETITION = 'high_competition';
    case IMPORT_ISSUES = 'import_issues';
    case ARRIVED_BROKEN = 'arrived_broken';
    case ARRIVED_SPOILED = 'arrived_spoiled';

    public static function labels(): array
    {
        return [
            'high_complaints'  => __('High complaints'),
            'slow_seller'      => __('Slow seller'),
            'high_competition' => __('High competition'),
            'import_issues'    => __('Import issues'),
            'arrived_broken'   => __('Arrived broken'),
            'arrived_spoiled'  => __('Arrived rotten or out of date'),
        ];
    }
}
