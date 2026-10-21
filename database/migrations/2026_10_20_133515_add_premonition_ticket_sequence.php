<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 22:40:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Enums\Helpers\Ticket\TicketTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SEQUENCE IF NOT EXISTS '.TicketTypeEnum::PREMONITION->sequence());
    }

    public function down(): void
    {
        DB::statement('DROP SEQUENCE IF EXISTS '.TicketTypeEnum::PREMONITION->sequence());
    }
};
