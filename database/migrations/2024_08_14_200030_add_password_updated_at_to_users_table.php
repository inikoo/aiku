<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Aug 2024 11:07:10 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Osiset\ShopifyApp\Util;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table(Util::getShopsTable(), function (Blueprint $table) {
            $table->date('password_updated_at')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table(Util::getShopsTable(), function (Blueprint $table) {
            $table->dropColumn('password_updated_at');
        });
    }
};
