<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('customer_comms', function (Blueprint $table) {
            $table->boolean('is_subscribed_to_review_reminder')->default(true);
            $table->dateTimeTz('review_reminder_unsubscribed_at')->nullable();
            $table->string('review_reminder_unsubscribed_author_type')->nullable()->comment('Customer|User');
            $table->string('review_reminder_unsubscribed_author_id')->nullable();
            $table->string('review_reminder_unsubscribed_origin_type')->nullable()->comment('EmailBulkRun|Mailshot|Website|Customer (Customer is used when a user unsubscribes from aiku UI)');
            $table->string('review_reminder_unsubscribed_origin_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customer_comms', function (Blueprint $table) {
            $table->dropColumn([
                'is_subscribed_to_review_reminder',
                'review_reminder_unsubscribed_at',
                'review_reminder_unsubscribed_author_type',
                'review_reminder_unsubscribed_author_id',
                'review_reminder_unsubscribed_origin_type',
                'review_reminder_unsubscribed_origin_id',
            ]);
        });
    }
};
