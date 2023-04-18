<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 08:18:53 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Seeders;

use App\Models\Assets\TariffCode;
use Illuminate\Database\Seeder;

class TariffCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $tariffCodes = [];

        foreach ($tariffCodes as $tariffCode) {
            TariffCode::create([
                'section'     => $tariffCode['section'],
                'hs_code'     => $tariffCode['hs_code'],
                'description' => $tariffCode['description'],
                'parent_id'   => $tariffCode['parent_id'],
                'level'       => $tariffCode['level'],
            ]);
        }
    }
}
