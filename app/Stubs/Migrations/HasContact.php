<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Jun 2023 15:09:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasContact
{
    public function contactFields(Blueprint $table, $withCompany = true, $withPersonalDetails = false, $withWebsite = false): Blueprint
    {
        $table->string('contact_name', 256)->collation('und_ci')->nullable();
        if ($withCompany) {
            $table->string('company_name', 256)->collation('und_ci')->nullable();
        }
        $table->string('email')->nullable()->collation('und_ci');
        $table->string('phone')->nullable();
        $table->string('identity_document_type')->nullable();
        $table->string('identity_document_number')->nullable()->collation('und_ci');
        if ($withPersonalDetails) {
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
        }
        if ($withWebsite) {
            $table->string('contact_website', 256)->nullable();
        }

        return $table;
    }
}
