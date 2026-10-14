<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:46:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_agreed_terms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->nullable()->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->string('subject_type');
            $table->unsignedInteger('subject_id');
            $table->string('term_type')->index();
            $table->string('value_type');
            $table->decimal('amount', 16, 4)->nullable();
            $table->unsignedSmallInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->string('value_text')->nullable();
            $table->decimal('tolerance', 16, 4)->nullable();
            $table->date('agreed_on');
            $table->unsignedSmallInteger('agreed_by_user_id')->nullable();
            $table->foreign('agreed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('agreed_with')->nullable();
            $table->unsignedBigInteger('evidence_message_id')->nullable();
            $table->foreign('evidence_message_id')->references('id')->on('supplier_messages')->nullOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('state')->default('active')->index();
            $table->unsignedInteger('superseded_by_id')->nullable();
            $table->foreign('superseded_by_id')->references('id')->on('supplier_agreed_terms')->nullOnDelete();
            $table->string('visibility')->default('internal');
            $table->boolean('show_on_po')->default(false);
            $table->timestampsTz();
            $table->index(['subject_type', 'subject_id', 'state']);
        });

        Schema::create('procurement_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->nullable()->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->string('subject_type');
            $table->unsignedInteger('subject_id');
            $table->string('category')->default('general')->index();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->string('severity')->default('info');
            $table->string('visibility')->default('internal');
            $table->unsignedInteger('purchase_order_id')->nullable()->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            $table->decimal('incident_amount', 16)->nullable();
            $table->unsignedSmallInteger('incident_currency_id')->nullable();
            $table->foreign('incident_currency_id')->references('id')->on('currencies');
            $table->string('incident_outcome')->nullable();
            $table->unsignedBigInteger('evidence_message_id')->nullable();
            $table->foreign('evidence_message_id')->references('id')->on('supplier_messages')->nullOnDelete();
            $table->unsignedSmallInteger('author_user_id')->nullable();
            $table->foreign('author_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->string('state')->default('active')->index();
            $table->dateTimeTz('resolved_at')->nullable();
            $table->unsignedSmallInteger('resolved_by_user_id')->nullable();
            $table->foreign('resolved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampsTz();
            $table->dateTimeTz('fetched_at')->nullable();
            $table->string('source_id')->nullable()->unique();
            $table->index(['subject_type', 'subject_id', 'state']);
        });

        DB::statement(<<<'SQL'
            insert into procurement_notes
                (group_id, organisation_id, subject_type, subject_id, body, author_user_id, author_name, state, created_at, updated_at, fetched_at, source_id)
            select audits.group_id, audits.organisation_id, audits.auditable_type, audits.auditable_id, audits.new_values->>'note',
                   users.id, audits.data->>'author',
                   case when audits.data->>'strikethrough' = 'true' then 'superseded' else 'active' end,
                   audits.created_at, audits.updated_at, audits.fetched_at, audits.source_id
            from audits
            left join users on users.id = audits.user_id and audits.user_type = 'User'
            where audits.event = 'note'
              and audits.auditable_type in ('PurchaseOrder', 'StockDelivery')
              and audits.group_id is not null
            order by audits.id
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_notes');
        Schema::dropIfExists('supplier_agreed_terms');
    }
};
