<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('product_development_design_sections', function (Blueprint $table) {
            $table->boolean('needs_compliance_text')->default(false);
        });

        DB::table('product_development_design_sections')->where('name', 'Label')->update(['needs_compliance_text' => true]);
    }

    public function down(): void
    {
        Schema::table('product_development_design_sections', function (Blueprint $table) {
            $table->dropColumn('needs_compliance_text');
        });
    }
};
