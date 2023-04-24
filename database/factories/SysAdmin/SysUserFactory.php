<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 01:29:41 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\SysAdmin;

use Illuminate\Database\Eloquent\Factories\Factory;

class SysUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'username'=> fake()->userName(),
            'password'=> fake()->password()
        ];
    }
}
