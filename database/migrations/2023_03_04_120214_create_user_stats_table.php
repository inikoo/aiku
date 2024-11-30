<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasUserStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasUserStats;
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table = $this->userStats($table);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
