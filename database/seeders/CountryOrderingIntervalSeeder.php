<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace Database\Seeders;

use App\Models\Catalogue\Shop;
use App\Models\Helpers\Country;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Seeder;

class CountryOrderingIntervalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $countries = Country::all()->pluck('id');
        $groups = Group::all();
        $organisations = Organisation::all();
        $shops = Shop::all();

        // this running every day
        foreach ($countries as $countryId) {
            $groupData = ['country_id' => $countryId];
            $organisationData = ['country_id' => $countryId];
            $shopData = ['country_id' => $countryId];

            foreach ($groups as $group) {
                $group->countryOrderingIntervals->create($groupData);
            }
            foreach ($organisations as $organisation) {
                $organisation->countryOrderingIntervals->create($organisationData);
            }
            foreach ($shops as $shop) {
                $shop->countryOrderingIntervals->create($shopData);
            }
        }


    }
}
