<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('group_user_organisation', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_user_id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('user_id')->index()->nullable();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['group_user_id', 'organisation_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_user_organisation');
    }
};
