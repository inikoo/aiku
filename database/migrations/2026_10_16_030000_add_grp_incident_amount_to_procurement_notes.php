<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('procurement_notes', function (Blueprint $table) {
            $table->decimal('grp_incident_amount', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('procurement_notes', function (Blueprint $table) {
            $table->dropColumn('grp_incident_amount');
        });
    }
};
