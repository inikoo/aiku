<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 12:33:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->date('date');
            $table->string('subject_type')->comment('Employee|Guest');
            $table->unsignedSmallInteger('subject_id');
            $table->dateTimeTz('start_at')->nullable();
            $table->dateTimeTz('end_at')->nullable();
            $table->unsignedSmallInteger('number_breaks')->default(0);
            $table->unsignedSmallInteger('number_time_trackers')->default(0);
            $table->unsignedSmallInteger('working_minutes')->default(0);
            $table->unsignedSmallInteger('breaks_minutes')->default(0);
            $table->unsignedSmallInteger('total_minutes')->default(0);
            $table->timestampsTz();
            $table->unique(['date','subject_type', 'subject_id']);
        });

        Schema::table('clockings', function (Blueprint $table) {
            $table->foreign('timesheet_id')->references('id')->on('timesheets');
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
