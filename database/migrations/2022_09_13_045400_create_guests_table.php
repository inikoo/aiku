<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:54:23 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->string('code')->index();

            // user status depends on this
            $table->boolean('status')->default(true)->index()->comment('linked to user status');
            //these are no normal, hydra-table from contact
            $table->string('name',256)->nullable()->index();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('identity_document_type')->nullable();
            $table->string('identity_document_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Make', 'Female', 'Other'])->nullable();
            //=====
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedBigInteger('organisation_source_id')->nullable();
            $table->unique(['organisation_id','organisation_source_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('guests');
    }
};
