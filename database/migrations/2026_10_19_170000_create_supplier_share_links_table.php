<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_share_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedInteger('supplier_id')->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->string('token_hash', 64)->unique();
            $table->string('email')->nullable()->comment('when set, a code sent here is asked before the page shows');
            $table->string('code_hash', 64)->nullable();
            $table->dateTimeTz('code_expires_at')->nullable();
            $table->unsignedSmallInteger('code_attempts')->default(0);
            $table->dateTimeTz('expires_at');
            $table->dateTimeTz('revoked_at')->nullable();
            $table->unsignedInteger('created_by_user_id')->nullable()->index();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedInteger('revoked_by_user_id')->nullable();
            $table->foreign('revoked_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTimeTz('last_viewed_at')->nullable();
            $table->unsignedInteger('number_views')->default(0);
            $table->timestampsTz();
        });

        Schema::create('supplier_share_link_views', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_share_link_id')->index();
            $table->foreign('supplier_share_link_id')->references('id')->on('supplier_share_links')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTimeTz('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_share_link_views');
        Schema::dropIfExists('supplier_share_links');
    }
};
