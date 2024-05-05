<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 23:18:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    public function up(): void
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table=$this->groupOrgRelationship($table);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->index()->collation('und_ns');
            $table->string('name')->index()->collation('und_ci');
            $table->string('department')->nullable();
            $table->string('team')->nullable();
            $table->jsonb('data');
            $table->unsignedSmallInteger('number_employees')->default(0);
            $table->unsignedSmallInteger('number_guests')->default(0);
            $table->unsignedSmallInteger('number_roles')->default(0);
            $table->double('number_work_time')->default(0);
            $table->decimal('share_work_time', 7, 6)->nullable();
            $table->boolean('locked')->default(true)->comment('Seeded job positions should be locked');
            $table->timestampsTz();
            $table->index(['code','organisation_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('job_positions');
    }
};
