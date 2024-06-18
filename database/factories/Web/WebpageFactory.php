<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 13 Jan 2024 13:37:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Database\Factories\Web;

use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebpageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'    => fake()->lexify(),
            'purpose' => WebpagePurposeEnum::INFO,
            'type'    => WebpageTypeEnum::CONTENT,
            'url'     => fake()->lexify(),
        ];
    }
}
