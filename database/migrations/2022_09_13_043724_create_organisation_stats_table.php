<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:22:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Auth\User\UserTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organisation_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('public.organisations')->onUpdate('cascade')->onDelete('cascade');


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
        Schema::dropIfExists('organisation_stats');
    }
};
