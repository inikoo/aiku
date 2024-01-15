<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 09 Aug 2023 11:25:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Helpers;

use Lorisleiva\Actions\Concerns\AsObject;

class ArrayWIthProbabilities
{
    use AsObject;

    public function getRandomElement(array $items): int|string|null
    {
        $totalProbability = 0;

        foreach ($items as $probability) {
            $totalProbability += $probability;
        }

        $stopAt             = rand(0, $totalProbability);
        $currentProbability = 0;

        foreach ($items as $item => $probability) {
            $currentProbability += $probability;
            if ($currentProbability >= $stopAt) {
                return $item;
            }
        }

        return array_key_first($items);
    }
}
