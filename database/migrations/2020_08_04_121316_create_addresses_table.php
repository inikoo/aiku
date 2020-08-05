<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        //https://github.com/commerceguys/addressing
        /*
            Country code (The two-letter country code)
            Administrative area
            Locality (City)
            Dependent Locality
            Postal code
            Sorting code
            Address line 1
            Address line 2
            Organization
            Given name (First name)
            Additional name (Middle name / Patronymic)
            Family name (Last name)

         */

        Schema::create(
            'addresses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('addresses');
    }
}
