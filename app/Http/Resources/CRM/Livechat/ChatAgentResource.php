<?php

namespace App\Http\Resources\CRM\Livechat;

use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatAgentResource extends JsonResource
{
    use WithChatAgentAuthorisation;

    public function toArray($request): array
    {
        $isDeletedInOrg = $this->deleted_at !== null;
        $orgSlug        = $this->organisation_slug;

        $data = [
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'name'                 => $this->name,
            'shops'                => $this->shops,
            'is_online'            => $this->isConnected(),
            'is_available'         => $this->is_available,
            'presence_status'      => $this->presenceStatus()->value,
            'last_heartbeat_at'    => $this->last_heartbeat_at,
            'current_chat_count'   => $this->current_chat_count,
            'max_concurrent_chats' => $this->max_concurrent_chats,
            'auto_accept'          => $this->auto_accept,
            'specialization'       => $this->specialization,
            'signature'            => $this->signature,
            'created_at'           => $this->created_at,
            'is_deleted_in_org'    => $isDeletedInOrg,
        ];

        if (!$isDeletedInOrg && $this->canBeEditedBy($request->user(), $orgSlug)) {
            $data['route_edit'] = [
                'name'       => 'grp.org.chat.agents.edit',
                'parameters' => [$orgSlug, $this->id],
            ];
        }

        return $data;
    }

    private function canBeEditedBy(?User $user, ?string $organisationSlug): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        $key = 'chat_supervises_'.$organisationSlug;
        if (!request()->attributes->has($key)) {
            $organisation = Organisation::where('slug', $organisationSlug)->first();
            request()->attributes->set($key, $organisation && $this->userSupervisesChatOnOrganisation($user, $organisation));
        }

        return request()->attributes->get($key);
    }
}
