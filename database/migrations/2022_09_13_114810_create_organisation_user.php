<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 19:48:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('organisation_id')->nullable()->constrained();

            $table->nullableMorphs('userable');
            $table->boolean('status')->default(true);
            $table->timestampsTz();
            $table->unique(['user_id', 'organisation_id']);
        });

        Schema::table('users', function($table) {
            $table->unsignedBigInteger('current_ui_organisation_id')->nullable();
            $table->foreign('current_ui_organisation_id')->references('id')->on('organisations');
        });
    }


    public function down()
    {
        Schema::table('users', function($table) {
            $table->dropColumn('current_ui_organisation_id');
        });
        Schema::dropIfExists('organisation_user');

    }
};
