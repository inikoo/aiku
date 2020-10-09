<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Utils extends Migration {
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

        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('code',2)->unique()->index();
            $table->string('code_iso3',3)->nullable()->index();
            $table->unsignedSmallInteger('code_iso_numeric')->nullable()->index();
            $table->unsignedInteger('geoname_id')->nullable()->index();

            $table->string('phone_code')->nullable();
            $table->string('currency_code')->nullable();

            $table->string('name');
            $table->string('continent');
            $table->string('capital');
            $table->string('timezone')->comment('Timezone in capital');

            $table->unsignedSmallInteger('shippers_count')->default(0);
            $table->jsonb('data');
            $table->timestampsTz();
        });

        Schema::create(
            'addresses', function (Blueprint $table) {
            $table->id();
            $table->string('contact')->nullable();
            $table->string('organization')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('sorting_code')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('locality')->nullable();
            $table->string('dependent_locality')->nullable();
            $table->string('administrative_area')->nullable();
            $table->string('country_code',2)->nullable()->index();
            $table->string('checksum')->index();
            $table->foreignId('owner_id')->nullable()->index();
            $table->string('owner_type')->nullable()->index();
            $table->unsignedSmallInteger('country_id')->nullable()->index();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->index(['checksum', 'owner_id','owner_type']);

            $table->timestampsTz();
        });

        Schema::create('addressables', function (Blueprint $table) {
            $table->bigIncrements('address_id');
            $table->foreignId('addressable_id')->index();
            $table->string('addressable_type')->index();
            $table->timestampsTz();
        });


        Schema::create('dates', function (Blueprint $table) {

            $table->mediumIncrements('id');
            $table->date('date')->unique();
            $table->string('holiday');
            $table->boolean('working');
            $table->json('data');
            $table->timestampsTz();
        });

        Schema::create('audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->morphs('auditable');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->unsignedBigInteger('ip_address')->nullable()->index();
            $table->unsignedBigInteger('user_agent')->nullable()->index();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type']);
        });

        Schema::create('images', function (Blueprint $table) {
            $table->id('id');
            $table->string('checksum')->index();
            $table->string('path')->index();
            $table->unsignedBigInteger('filesize')->index();
            $table->unsignedBigInteger('pixels')->index();
            $table->boolean('public')->default(false);
            $table->jsonb('data');
            $table->timestampsTz();
        });

        Schema::create('imageable', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('image_id');
            $table->foreign('image_id')->references('id')->on('images');

            $table->string('imageable_id')->index();
            $table->string('imageable_type')->index();

            $table->string('scope')->index();
            $table->boolean('public')->default(false);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->index(['imageable_id', 'imageable_type']);
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('imageable');
        Schema::dropIfExists('images');
        Schema::dropIfExists('audits');
        Schema::dropIfExists('dates');
        Schema::dropIfExists('addressables');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('countries');



    }
}
