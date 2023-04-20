<?php

namespace Tests\Feature;

use Database\Seeders\RestoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;


    protected string $seeder = RestoreDatabaseSeeder::class;

    public function test_create_group()
    {
        $this->artisan('create:group aw awa USD')->assertSuccessful();
    }

    public function test_create_group_without_correct_currency()
    {
        $this->artisan('create:group aq awq XXX')->assertFailed();
    }


    public function test_duplicate_groups()
    {
        $this->artisan('create:group aw awa USD')->assertSuccessful();
        $this->artisan('create:group aw awa USD')->assertFailed();
    }

}
