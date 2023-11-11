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
        $table->boolean('status')->default(true)->index();
        $table->boolean('is_private')->default(false)->index();
        $table->string('slug')->unique()->collation('und_ns');
        $table->string('code')->index()->collation('und_ns');
        $table->string('owner_type')->comment('Who can edit this model Organisation|Agent|Supplier');
        $table->unsignedInteger('owner_id');
        $table->string('name')->nullable()->collation('und_ns');
        $table->unsignedBigInteger('image_id')->nullable();
        $table->foreign('image_id')->references('id')->on('group_media');

        $table = $this->contactFields(table: $table, withWebsite: true);


        $table->unsignedInteger('address_id')->nullable()->index();
        $table->foreign('address_id')->references('id')->on('group_addresses');
        $table->jsonb('location');

        $table->unsignedSmallInteger('currency_id');
        $table->foreign('currency_id')->references('id')->on('public.currencies');
        $table->jsonb('settings');
        $table->jsonb('shared_data');
        $table->jsonb('tenant_data');

        $table->timestampsTz();
        $table->softDeletesTz();
        $table->string('source_type')->index()->nullable();
        $table->unsignedInteger('source_id')->index()->nullable();
        $table->index(['owner_id', 'owner_type']);

        return $table;
    }
}
