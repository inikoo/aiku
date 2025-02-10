<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Feb 2025 19:19:53 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('group_comms_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_outboxes_type_send_invoice_to_customer')->default(0);
        });

        Schema::table('organisation_comms_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_outboxes_type_send_invoice_to_customer')->default(0);
        });

        Schema::table('shop_comms_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_outboxes_type_send_invoice_to_customer')->default(0);
        });

        Schema::table('post_room_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_outboxes_type_send_invoice_to_customer')->default(0);
        });

        Schema::table('org_post_room_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_outboxes_type_send_invoice_to_customer')->default(0);
        });
    }


    public function down(): void
    {
        Schema::table('group_comms_stats', function (Blueprint $table) {
            $table->dropColumn('number_outboxes_type_send_invoice_to_customer');
        });

        Schema::table('organisation_comms_stats', function (Blueprint $table) {
            $table->dropColumn('number_outboxes_type_send_invoice_to_customer');
        });

        Schema::table('shop_comms_stats', function (Blueprint $table) {
            $table->dropColumn('number_outboxes_type_send_invoice_to_customer');
        });

        Schema::table('post_room_stats', function (Blueprint $table) {
            $table->dropColumn('number_outboxes_type_send_invoice_to_customer');
        });

        Schema::table('org_post_room_stats', function (Blueprint $table) {
            $table->dropColumn('number_outboxes_type_send_invoice_to_customer');
        });


    }
};
