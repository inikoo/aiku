<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:37:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('tenant_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');


            $table->unsignedSmallInteger('number_employees')->default(0);

            $employeeStates = ['hired', 'working', 'left'];
            foreach ($employeeStates as $employeeState) {
                $table->unsignedSmallInteger('number_employees_state_'.$employeeState)->default(0);
            }

            $table->unsignedSmallInteger('number_guests')->default(0);
            $table->unsignedSmallInteger('number_guests_status_active')->default(0);
            $table->unsignedSmallInteger('number_guests_status_inactive')->default(0);

            $table->unsignedSmallInteger('number_users')->default(0);
            $table->unsignedSmallInteger('number_users_status_active')->default(0);
            $table->unsignedSmallInteger('number_users_status_inactive')->default(0);

            $userTypes = ['tenant', 'employee', 'guest', 'supplier', 'agent', 'customer'];
            foreach ($userTypes as $userType) {
                $table->unsignedSmallInteger('number_users_type_'.$userType)->default(0);
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


    public function down()
    {
        Schema::dropIfExists('tenant_stats');
    }
};
