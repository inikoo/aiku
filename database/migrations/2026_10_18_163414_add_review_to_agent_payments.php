<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->boolean('needs_review')->default(false)->index();
            $table->string('review_reason')->nullable();
            $table->jsonb('data')->default('{}');
        });
    }

    public function down(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->dropColumn(['needs_review', 'review_reason', 'data']);
        });
    }
};
