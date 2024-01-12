<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 13:27:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_subjects')->default(0);
            $table->string('tag_slug')->nullable()->unique()->collation('und_ns');
            $table->string('label')->nullable()->index()->collation('und_ci');
        });
    }


    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn(['number_subjects', 'label','tag_slug']);
        });
    }
};
