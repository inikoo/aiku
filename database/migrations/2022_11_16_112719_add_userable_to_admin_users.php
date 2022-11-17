<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 16 Nov 2022 19:27:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->nullableMorphs('userable');
            $table->string('email')->nullable()->unique();
            $table->dropColumn(['admin_id']);
        });
    }


    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn(['userable_type', 'userable_id', 'email']);
            $table->unsignedSmallInteger('admin_id')->nullable();
        });
    }
};
