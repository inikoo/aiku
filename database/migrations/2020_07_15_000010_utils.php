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
            $table->id();
            $table->unsignedBigInteger('address_id')->index();
            $table->foreignId('addressable_id')->index();
            $table->string('addressable_type')->index();
            $table->string('scope')->nullable()->index();

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
            $table->unsignedBigInteger('communal_image_id')->nullable()->index();
            $table->string('checksum')->nullable()->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
            $table->unsignedMediumInteger('tenant_id');

        });

        Schema::create('image_models', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('image_id');
            $table->foreign('image_id')->references('id')->on('images');

            $table->string('imageable_type')->nullable()->index();
            $table->unsignedBigInteger('imageable_id')->nullable()->index();

            $table->string('scope')->index();
            $table->smallInteger('precedence')->default(0);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->index(['imageable_id', 'imageable_type','scope']);
            $table->unique(['image_id','imageable_id', 'imageable_type','scope']);

        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id('id');
            $table->string('checksum')->unique()->index();
            $table->unsignedBigInteger('filesize')->index();
            $table->binary('attachment_data');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->unsignedMediumInteger('legacy_id')->nullable()->index();
            $table->unsignedMediumInteger('tenant_id');

        });

        Schema::create('attachment_models', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('attachment_id');
            $table->foreign('attachment_id')->references('id')->on('attachments');

            $table->string('attachmentable_type')->nullable()->index();
            $table->unsignedBigInteger('attachmentable_id')->nullable()->index();

            $table->string('scope')->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->index(['attachmentable_id', 'attachmentable_type','scope']);
            $table->unique(['attachment_id','attachmentable_id', 'attachmentable_type','scope']);

        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('attachment_models');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('image_models');
        Schema::dropIfExists('images');
        Schema::dropIfExists('audits');
        Schema::dropIfExists('dates');
        Schema::dropIfExists('addressables');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('countries');



    }
}
