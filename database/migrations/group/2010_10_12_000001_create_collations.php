<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Jun 2023 03:22:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        DB::unprepared('CREATE COLLATION und_ns_ci_ai (PROVIDER = icu,DETERMINISTIC = FALSE,LOCALE = "und-u-kn-true-ks-level1");');
        DB::unprepared('CREATE COLLATION und_ns_ci (PROVIDER = icu,DETERMINISTIC = FALSE,LOCALE = "und-u-kn-true-ks-level2");');
        DB::unprepared('CREATE COLLATION und_ns (PROVIDER = icu,LOCALE = "und-u-kn-true");');
    }

    public function down(): void
    {
    }
};
