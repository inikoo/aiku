<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('guru_since')->nullable();
            $table->date('guru_until')->nullable();
        });

        DB::statement("
            INSERT INTO manufacture_pay_bands (group_id, organisation_id, production_id, code, name, hourly_rate, target_multiplier, requires_approval, effective_from, created_at, updated_at)
            SELECT group_id, organisation_id, production_id, 'GURU', 'Guru bonus', 1.00, NULL, false, min(effective_from), now(), now()
            FROM manufacture_pay_bands
            WHERE code = '0'
            GROUP BY group_id, organisation_id, production_id
            ON CONFLICT DO NOTHING
        ");
    }

    public function down(): void
    {
        DB::table('manufacture_pay_bands')->where('code', 'GURU')->delete();

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['guru_since', 'guru_until']);
        });
    }
};
