<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 09:50:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('payment_service_providers', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('type')->index();
            $table->string('code')->index()->collation('und_ns_ci');
            $table->string('slug')->unique()->collation('und_ns');
            $table->jsonb('data');
            $table->dateTimeTz('last_used_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->index()->nullable();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('payment_service_providers');
    }
};
