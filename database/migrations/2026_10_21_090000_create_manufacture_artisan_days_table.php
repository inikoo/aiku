<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 16:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('manufacture_hours_tolerances', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedSmallInteger('production_id')->index();
            $table->foreign('production_id')->references('id')->on('productions');
            $table->unsignedSmallInteger('tolerance_minutes');
            $table->date('effective_from');
            $table->unsignedSmallInteger('set_by_user_id')->nullable();
            $table->foreign('set_by_user_id')->references('id')->on('users');
            $table->timestampsTz();
            $table->unique(['production_id', 'effective_from']);
        });

        Schema::create('manufacture_artisan_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedSmallInteger('production_id')->index();
            $table->foreign('production_id')->references('id')->on('productions');
            $table->unsignedSmallInteger('employee_id')->index();
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->unsignedSmallInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->date('date')->index();
            $table->timestampTz('shift_start')->nullable();
            $table->timestampTz('shift_end')->nullable();
            $table->unsignedSmallInteger('expected_minutes')->nullable();
            $table->unsignedSmallInteger('recorded_minutes')->default(0);
            $table->unsignedSmallInteger('tolerance_minutes');
            $table->unsignedSmallInteger('unchecked_running_jobs')->default(0);
            $table->string('status')->index();
            $table->boolean('is_warning_open')->default(false)->index();
            $table->string('resolution')->nullable();
            $table->string('resolution_reason')->nullable();
            $table->text('resolution_note')->nullable();
            $table->unsignedSmallInteger('resolved_minutes')->nullable();
            $table->unsignedSmallInteger('resolved_by_user_id')->nullable();
            $table->foreign('resolved_by_user_id')->references('id')->on('users');
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();
            $table->unique(['production_id', 'employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacture_artisan_days');
        Schema::dropIfExists('manufacture_hours_tolerances');
    }
};
