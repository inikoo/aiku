<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jun 2026 21:08:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Models\Chat\ChatAgent;
use App\Models\SysAdmin\User;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreChatAgent
{
    use AsAction;

    public function handle(array $data): ChatAgent
    {
        DB::beginTransaction();

        try {
            $user = User::find($data['user_id']);
            if (!$user) {
                throw new HttpResponseException(response()->json([
                   'success' => false,
                   'message' => 'User not found'
                ], 404));
            }

            $chatAgentData = [
              'user_id'              => $data['user_id'],
              'max_concurrent_chats' => $data['max_concurrent_chats'] ?? 10,
              'is_online'            => $data['is_online'] ?? false,
              'is_available'         => $data['is_available'] ?? true,
              'current_chat_count'   => 0,
              'specialization'       => $data['specialization'] ?? [],
              'auto_accept'          => $data['auto_accept'] ?? false,
            ];

            $chatAgent = ChatAgent::create($chatAgentData);
            DB::commit();

            return $chatAgent;

        } catch (Exception $e) {
            DB::rollBack();

            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Failed to create chat agent profile: ' . $e->getMessage()
                ], 500)
            );
        }
    }
}
