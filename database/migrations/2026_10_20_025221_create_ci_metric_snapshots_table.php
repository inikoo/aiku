<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 10:52:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ci_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('head_sha', 40)->nullable();
            $table->unsignedBigInteger('github_run_id')->nullable();
            $table->timestampTz('measured_at');
            $table->jsonb('summary')->default('{}');
            $table->jsonb('breakdown')->default('{}');
            $table->timestampsTz();
            $table->unique(['type', 'head_sha']);
            $table->index(['type', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ci_metric_snapshots');
    }
};
