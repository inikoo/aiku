<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Reviewed: Thu, 06 Jun 2024 14:36:49 Central European Summer Time, Plane Malaga - Abu Dhabi
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Task\TaskStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code', 64)->index()->collation('und_ns');
            $table->string('name');
            $table->text('description')->nullable()->fulltext();
            $table->dateTimeTz('start_date')->nullable();
            $table->dateTimeTz('complete_date')->nullable();
            $table->dateTimeTz('deadline')->nullable();
            $table->string('status')->default(TaskStatusEnum::PENDING->value);
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
