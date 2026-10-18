<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Language;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * The languages a trade unit name is translated into: the language of every open shop, so a new
 * shop brings its language with it. Extra shop languages are left out: they add Indonesian and
 * Japanese, which no AW market prints or sells in.
 */
class GetTradeUnitNameTranslationLanguages
{
    use AsObject;

    /**
     * @return array<int, string>
     */
    public function handle(Group $group): array
    {
        return Language::whereIn(
            'id',
            Shop::where('group_id', $group->id)->where('state', ShopStateEnum::OPEN)->select('language_id')
        )
            ->where('code', '!=', 'en')
            ->orderBy('code')
            ->pluck('code')
            ->all();
    }
}
