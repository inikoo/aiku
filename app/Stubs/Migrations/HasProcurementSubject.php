<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Jun 2023 15:02:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasProcurementSubject
{
    use HasContact;
    public function procurementSubject(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('group_id');
        $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
        if($table->getTable()=='suppliers') {
            $table->unsignedSmallInteger('agent_id')->nullable()->index();
            $table->foreign('agent_id')->references('id')->on('agents')->onUpdate('cascade')->onDelete('cascade');

        }
        $table->boolean('status')->default(true)->index();
        $table->string('slug')->unique()->collation('und_ns');
        $table->string('code')->index()->collation('und_ns');
        $table->string('name')->nullable()->collation('und_ns');
        $table->unsignedInteger('image_id')->nullable();
        $table->foreign('image_id')->references('id')->on('media');
        $table = $this->contactFields(table: $table, withWebsite: true);
        $table->unsignedInteger('address_id')->nullable()->index();
        $table->foreign('address_id')->references('id')->on('addresses');
        $table->jsonb('location');
        $table->unsignedSmallInteger('currency_id');
        $table->foreign('currency_id')->references('id')->on('currencies');
        $table->jsonb('settings');
        $table->jsonb('data');
        $table->timestampsTz();
        $table->softDeletesTz();
        $table->string('source_type')->index()->nullable();
        $table->string('source_id')->index()->nullable();
        return $table;
    }
}
