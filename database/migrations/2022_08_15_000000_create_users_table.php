<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Sept 2022 23:17:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\SysAdmin\User\UserAuthTypeEnum;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSoftDeletes;
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->string('slug')->unique()->collation('und_ns');
            $table->boolean('status')->default(true);
            $table->string('username')->unique()->collation('und_ns');
            $table->string('password')->nullable();
            $table->string('type')->nullable()->comment('same as parent_type excluding Organisation, for use in UI');
            $table->string('auth_type')->default(UserAuthTypeEnum::DEFAULT->value);
            $table->string('contact_name')->nullable()->collation('und_ns')->comment('no-normalised depends on parent');
            $table->string('email')->nullable()->collation('und_ns')->comment('mirror group_users.email');
            $table->text('about')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('parent_type')->nullable();
            $table->rememberToken();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->unsignedSmallInteger('language_id')->default(68);
            $table->foreign('language_id')->references('id')->on('languages');
            $table->unsignedInteger('avatar_id')->nullable();
            $table->timestampsTz();
            $table=$this->softDeletes($table);
            $table->unsignedInteger('source_id')->nullable()->unique();
            $table->string('legacy_password')->nullable()->index()->comment('source password');

        });
        DB::statement('CREATE INDEX ON users USING gin (contact_name gin_trgm_ops) ');

    }


    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
