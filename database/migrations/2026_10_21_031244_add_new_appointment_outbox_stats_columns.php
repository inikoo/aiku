<?php

use App\Enums\Comms\Outbox\OutboxCodeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    private array $statsTables = [
        'group_comms_stats',
        'organisation_comms_stats',
        'shop_comms_stats',
        'post_room_stats',
        'org_post_room_stats',
    ];

    private function statsColumn(): string
    {
        return 'number_outboxes_type_'.OutboxCodeEnum::NEW_APPOINTMENT->snake();
    }

    public function up(): void
    {
        foreach ($this->statsTables as $statsTable) {
            if (!Schema::hasColumn($statsTable, $this->statsColumn())) {
                Schema::table($statsTable, function (Blueprint $table) {
                    $table->unsignedInteger($this->statsColumn())->default(0);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->statsTables as $statsTable) {
            if (Schema::hasColumn($statsTable, $this->statsColumn())) {
                Schema::table($statsTable, function (Blueprint $table) {
                    $table->dropColumn($this->statsColumn());
                });
            }
        }
    }
};
