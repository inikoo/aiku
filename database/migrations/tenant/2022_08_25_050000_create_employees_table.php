<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:07:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Enums\HumanResources\Employee\EmployeeTypeEnum;
use App\Stubs\Migrations\HasContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasContact;

    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');

            $table=$this->contactFields(table:$table, withCompany: false, withPersonalDetails: true);

            $table->string('worker_number')->nullable()->collation('und_ns_ci');
            $table->string('job_title')->nullable()->collation('und_ns_ci_ai');
            $table->string('type')->default(EmployeeTypeEnum::EMPLOYEE->value);
            $table->string('state')->default(EmployeeStateEnum::WORKING->value);
            $table->date('employment_start_at')->nullable();
            $table->date('employment_end_at')->nullable();
            $table->string('emergency_contact', 1024)->nullable()->collation('und_ns_ci_ai');
            $table->jsonb('salary')->nullable();
            $table->jsonb('working_hours')->nullable();
            $table->decimal('week_working_hours', 4)->default(0);
            $table->jsonb('data');
            $table->jsonb('job_position_scopes');
            $table->jsonb('errors');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });

        DB::statement('CREATE INDEX ON employees USING gin (contact_name gin_trgm_ops) ');
    }


    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
