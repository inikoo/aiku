<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('languages')->where('code', 'ro')->update(['status' => '1', 'native_name' => 'Română']);
    }

    public function down(): void
    {
        DB::table('languages')->where('code', 'ro')->update(['status' => '0', 'native_name' => 'Romanian']);
    }
};
