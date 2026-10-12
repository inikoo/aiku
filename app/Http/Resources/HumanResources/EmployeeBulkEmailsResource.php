<?php

namespace App\Http\Resources\HumanResources;

use App\Models\SysAdmin\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $subject
 * @property string $body
 * @property int $number_recipients
 * @property array<int, array{path: string, name: string, size?: int}>|null $attachments
 * @property User|null $sender
 * @property \Illuminate\Support\Carbon $created_at
 */
class EmployeeBulkEmailsResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        $attachments = array_values($this->attachments ?? []);

        return [
            'id'                => $this->id,
            'subject'           => $this->subject,
            'excerpt'           => $this->excerpt(),
            'body'              => $this->body,
            'number_recipients' => $this->number_recipients,
            'recipients_sample' => $this->resource->relationLoaded('recipientsSample')
                ? $this->resource->getRelation('recipientsSample')->values()->all()
                : [],
            'attachments'       => array_map(fn (array $attachment, int $index): array => [
                'index'     => $index,
                'name'      => $attachment['name'],
                'size'      => $attachment['size'] ?? null,
                'available' => Storage::disk('local')->exists($attachment['path']),
            ], $attachments, array_keys($attachments)),
            'sender'            => $this->sender ? [
                'name'   => $this->sender->contact_name ?: $this->sender->username,
                'avatar' => $this->sender->image ? $this->sender->imageSources(64, 64) : null,
            ] : null,
            'created_at'        => $this->created_at,
        ];
    }

    private function excerpt(): string
    {
        $text = html_entity_decode(strip_tags(preg_replace('/<(br|hr|\/(p|div|li|h[1-6]|blockquote|tr|td|th))\b[^>]*>/i', '$0 ', (string) $this->body)), ENT_QUOTES | ENT_HTML5);

        return Str::limit(Str::squish($text), 240);
    }
}
