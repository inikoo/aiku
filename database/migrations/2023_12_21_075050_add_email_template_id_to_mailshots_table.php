<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->unsignedSmallInteger('email_template_id')->nullable()->after('recipients_recipe');
            $table->foreign('email_template_id')->references('id')->on('email_templates');
        });
    }

    public function down(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->dropColumn('email_template_id');
        });
    }
};
