<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:47:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\AgentTenant;

use App\Enums\EnumHelperTrait;

enum AgentTypeEnum: string
{
    use EnumHelperTrait;

    case YES                = 'yes';
    case NO                 = 'no';


    public static function optionLabels(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return  [$case->value=>$case->optionLabel()];
        })->all();
    }

    public function optionLabel(): array
    {
        return match ($this) {
            AgentTypeEnum::YES => [
                'label' => __('yes'),

            ],
            AgentTypeEnum::NO => [
                'label' => __('no'),

            ],
        };
    }

}
