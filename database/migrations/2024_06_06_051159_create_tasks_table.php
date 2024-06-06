<?php

use App\Enums\Task\TaskStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code', 64)->index()->collation('und_ns');
            $table->string('name'); 
            $table->text('description')->nullable()->fulltext();
            $table->dateTimeTz('start_date')->nullable(); 
            $table->dateTimeTz('complete_date')->nullable(); 
            $table->dateTimeTz('deadline')->nullable();
            $table->string('status')->default(TaskStatusEnum::PENDING->value);
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
