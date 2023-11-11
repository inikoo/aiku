<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:16:52 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\MediaTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use MediaTable;
    public function up(): void
    {
        Schema::create('group_media', function (Blueprint $table) {
            $this->mediaFields($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_media');
    }
};
