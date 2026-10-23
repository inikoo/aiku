<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('chat_ai_runs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->string('kind')->index();
            $table->jsonb('teams');
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('started_by_user_id')->nullable();
            $table->foreign('started_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('started_at')->index();
            $table->timestampTz('finished_at')->nullable();
            $table->jsonb('counts');
            $table->timestampsTz();
        });

        Schema::create('chat_ai_outcomes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->cascadeOnDelete();
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();

            $table->unsignedInteger('chat_session_id')->nullable();
            $table->foreign('chat_session_id')->references('id')->on('chat_sessions')->cascadeOnDelete();
            $table->unsignedInteger('meta_chat_session_id')->nullable();
            $table->foreign('meta_chat_session_id')->references('id')->on('meta_chat_sessions')->cascadeOnDelete();
            $table->string('channel');

            $table->string('outcome')->index();
            $table->string('reason', 500)->nullable();
            $table->unsignedInteger('chat_ai_draft_id')->nullable();
            $table->foreign('chat_ai_draft_id')->references('id')->on('chat_ai_drafts')->nullOnDelete();
            $table->unsignedBigInteger('note_event_id')->nullable();
            $table->foreign('note_event_id')->references('id')->on('chat_events')->nullOnDelete();
            $table->unsignedBigInteger('trigger_message_id')->nullable();
            $table->unsignedInteger('run_id')->nullable()->index();
            $table->foreign('run_id')->references('id')->on('chat_ai_runs')->nullOnDelete();

            $table->timestampsTz();

            $table->index(['shop_id', 'created_at']);
            $table->index(['chat_session_id', 'trigger_message_id']);
            $table->index(['meta_chat_session_id', 'trigger_message_id']);
        });

        DB::statement('ALTER TABLE chat_ai_outcomes ADD CONSTRAINT chat_ai_outcomes_one_session CHECK ((chat_session_id IS NULL) <> (meta_chat_session_id IS NULL))');

        foreach (['chat_session_id', 'meta_chat_session_id'] as $column) {
            DB::statement(
                "CREATE UNIQUE INDEX chat_ai_outcomes_one_per_trigger_{$column}
                 ON chat_ai_outcomes ({$column}, trigger_message_id)
                 WHERE {$column} IS NOT NULL AND trigger_message_id IS NOT NULL"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_ai_outcomes');
        Schema::dropIfExists('chat_ai_runs');
    }
};
