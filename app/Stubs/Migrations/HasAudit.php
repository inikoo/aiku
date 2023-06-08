<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 15:57:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasAudit
{
    public function getAuditFields(Blueprint $table): Blueprint
    {
        $morphPrefix = config('audit.user.morph_prefix', 'user');

        $table->bigIncrements('id');
        $table->string($morphPrefix.'_type')->nullable();
        $table->unsignedBigInteger($morphPrefix.'_id')->nullable();
        $table->string('event');
        $table->morphs('auditable');
        $table->text('old_values')->nullable();
        $table->text('new_values')->nullable();
        $table->text('url')->nullable();
        $table->ipAddress()->nullable();
        $table->string('user_agent', 1023)->nullable();
        $table->string('tags')->nullable();
        $table->timestamps();

        $table->index([$morphPrefix.'_id', $morphPrefix.'_type']);

        return $table;
    }
}
