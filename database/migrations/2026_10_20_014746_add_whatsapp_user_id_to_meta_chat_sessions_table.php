<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 14:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('meta_chat_sessions', function (Blueprint $table) {
            $table->string('whatsapp_user_id', 150)->nullable()->index()->comment('WhatsApp business-scoped user id (BSUID), the only address of a contact who uses a username and hides their phone');
        });
    }

    public function down(): void
    {
        Schema::table('meta_chat_sessions', function (Blueprint $table) {
            $table->dropColumn('whatsapp_user_id');
        });
    }
};
