<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 28 Feb 2025 16:10:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('group_comms_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('organisation_comms_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('shop_comms_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });
        Schema::table('post_room_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('org_post_room_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('mailshot_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('outbox_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('email_bulk_run_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });

        Schema::table('email_ongoing_run_stats', function (Blueprint $table) {
            $table->unsignedBigInteger("number_dispatched_emails_state_delay")->default(0);
        });
    }


    public function down(): void
    {
        Schema::table('group_comms_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('organisation_comms_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('shop_comms_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('post_room_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('org_post_room_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('mailshot_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('outbox_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('email_bulk_run_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });

        Schema::table('email_ongoing_run_stats', function (Blueprint $table) {
            $table->dropColumn("number_dispatched_emails_state_delay");
        });
    }
};
