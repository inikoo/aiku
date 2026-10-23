<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 17:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('manufacture_preparation_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedSmallInteger('production_id')->index();
            $table->foreign('production_id')->references('id')->on('productions');
            $table->string('name');
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('preparation_reason_id')->nullable()->index();
            $table->foreign('preparation_reason_id')->references('id')->on('manufacture_preparation_reasons');
            $table->text('preparation_reason_note')->nullable();
            $table->text('preparation_reason_note_en')->nullable();
            $table->unsignedSmallInteger('preparation_reason_note_language_id')->nullable();
            $table->foreign('preparation_reason_note_language_id')->references('id')->on('languages');
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->dropForeign(['preparation_reason_id']);
            $table->dropForeign(['preparation_reason_note_language_id']);
            $table->dropColumn(['preparation_reason_id', 'preparation_reason_note', 'preparation_reason_note_en', 'preparation_reason_note_language_id']);
        });

        Schema::dropIfExists('manufacture_preparation_reasons');
    }
};
