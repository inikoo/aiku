<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:45:59 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Auth\User\UserTypeEnum;
use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tenant_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('number_employees')->default(0);

            foreach (EmployeeStateEnum::cases() as $employeeState) {
                $table->unsignedSmallInteger('number_employees_state_'.$employeeState->snake())->default(0);
            }

            $table->unsignedSmallInteger('number_guests')->default(0);
            $table->unsignedSmallInteger('number_guests_status_active')->default(0);
            $table->unsignedSmallInteger('number_guests_status_inactive')->default(0);

            $table->unsignedSmallInteger('number_users')->default(0);
            $table->unsignedSmallInteger('number_users_status_active')->default(0);
            $table->unsignedSmallInteger('number_users_status_inactive')->default(0);


            foreach (UserTypeEnum::cases() as $userType) {
                $table->unsignedSmallInteger('number_users_type_'.$userType->snake())->default(0);
            }

            $table->unsignedSmallInteger('number_images')->default(0);
            $table->unsignedBigInteger('filesize_images')->default(0);
            $table->unsignedSmallInteger('number_attachments')->default(0);
            $table->unsignedBigInteger('filesize_attachments')->default(0);


            $table->boolean('has_fulfilment')->default('false');
            $table->boolean('has_dropshipping')->default('false');
            $table->boolean('has_production')->default('false');
            $table->boolean('has_agents')->default('false');


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenant_stats');
    }
};
