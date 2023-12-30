<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 27 Oct 2022 20:55:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('web_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('type')->index();
            $table->unsignedSmallInteger('website_id')->index();
            $table->foreign('website_id')->references('id')->on('websites');
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->boolean('status')->default(true)->index();
            $table->string('username')->index();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('auth_type')->default(WebUserAuthTypeEnum::DEFAULT->value);
            $table->rememberToken();
            $table->unsignedSmallInteger('number_api_tokens')->default(0);
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['website_id', 'email']);
            $table->unique(['website_id', 'username']);
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('web_users');
    }
};
