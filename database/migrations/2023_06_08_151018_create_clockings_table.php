<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Jun 2023 03:30:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('clockings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('type')->index();
            $table->string('subject_type')->nullable();
            $table->unsignedInteger('subject_id')->nullable();
            $table->unsignedInteger('time_tracker_id')->index()->nullable();
            $table->unsignedInteger('workplace_id')->nullable()->index();
            $table->foreign('workplace_id')->references('id')->on('workplaces');
            $table->unsignedInteger('clocking_machine_id')->nullable()->index();
            $table->foreign('clocking_machine_id')->references('id')->on('clocking_machines');
            $table->dateTimeTz('clocked_at');
            $table->string('generator_type')->nullable();
            $table->unsignedInteger('generator_id')->nullable();
            $table->text('notes')->nullable();

            $table->timestampsTz();
            $table->softDeletes();
            $table->nullableMorphs('deleted_by');
            $table->string('source_id')->nullable()->unique();
            $table->unique(['subject_type', 'subject_id']);
            $table->unique(['generator_type', 'generator_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('clockings');
    }
};
