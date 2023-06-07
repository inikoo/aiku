<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Sept 2022 23:17:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_user_id');
            $table->boolean('status')->default(true);
            $table->string('username')->unique()->collation('und_ns_ci')->comment('mirror group_users.username');
            $table->string('password')->comment('mirror group_users.password');
            $table->string('email')->nullable()->collation('und_ns_ci')->comment('mirror group_users.email');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('parent_type')->nullable();
            $table->rememberToken();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
            $table->unique(['parent_type','parent_id']);

        });
        DB::statement('CREATE INDEX ON users USING gin (contact_name gin_trgm_ops) ');

    }


    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
