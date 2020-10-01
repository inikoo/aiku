<?php

use Database\Seeders\CountrySeeder;
use Illuminate\Database\Seeder;
use Spatie\Multitenancy\Models\Tenant as Tenanto;

class DatabaseSeeder extends Seeder {


    public function run() {
        Tenanto::checkCurrent() ? $this->runTenantSpecificSeeders() : $this->runLandlordSpecificSeeders();
    }

    public function runTenantSpecificSeeders() {
        $this->call(EmployeeSeeder::class);
        $this->call(ClockingMachineSeeder::class);

        $this->call(WarehouseSeeder::class);
        $this->call(StoreSeeder::class);

    }

    public function runLandlordSpecificSeeders() {
        $this->call(CountrySeeder::class);

    }


}
