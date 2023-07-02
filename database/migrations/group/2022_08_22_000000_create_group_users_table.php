
<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 14:21:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Auth\User\UserAuthTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('group_users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('username')->unique();
            $table->string('password')->nullable();
            $table->string('auth_type')->default(UserAuthTypeEnum::DEFAULT->value);
            $table->string('contact_name')->nullable()->collation('und_ns')->comment('no-normalised depends on parent');
            $table->string('email')->nullable()->collation('und_ns')->comment('mirror group_users.email');
            $table->string('about')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('avatar_id')->nullable();
            $table->foreign('avatar_id')->references('id')->on('group_media');
            $table->jsonb('data')->nullable();
            $table->unsignedSmallInteger('number_users')->default(0);
            $table->unsignedSmallInteger('number_active_users')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('legacy_password')->nullable()->index()->comment('source password');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_users');
    }
};
