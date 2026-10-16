<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-16h-55m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentComment;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentComments
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(ProductDevelopment $productDevelopment): array
    {
        $comments = $productDevelopment->comments()->with(['user', 'media'])->orderBy('id')->get();
        $replies  = $comments->whereNotNull('parent_id')->groupBy('parent_id');

        $mentionables = $productDevelopment->members()->with('user')->get()->pluck('user')
            ->push($productDevelopment->owner)
            ->filter()
            ->unique('id')
            ->sortBy(fn (User $user) => $user->contact_name ?: $user->username)
            ->map(fn (User $user) => [
                'id'       => $user->id,
                'name'     => $user->contact_name ?: $user->username,
                'username' => $user->username,
                'avatar'   => $user->image_id ? $user->imageSources(48, 48) : null,
            ])
            ->values()
            ->all();

        return [
            'threads'      => $comments->whereNull('parent_id')->sortByDesc('id')->map(fn (ProductDevelopmentComment $comment) => [
                ...$this->comment($comment),
                'replies' => $replies->get($comment->id, collect())->map(fn (ProductDevelopmentComment $reply) => $this->comment($reply))->values()->all(),
            ])->values()->all(),
            'mentionables' => $mentionables,
            'route'        => ['name' => 'grp.models.product_development.comments.store', 'parameters' => ['productDevelopment' => $productDevelopment->id]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function comment(ProductDevelopmentComment $comment): array
    {
        return [
            'id'         => $comment->id,
            'body'       => $comment->body,
            'user_id'    => $comment->user_id,
            'user'       => $comment->user?->contact_name ?: $comment->user?->username,
            'username'   => $comment->user?->username,
            'avatar'     => $comment->user?->image_id ? $comment->user->imageSources(48, 48) : null,
            'created_at' => $comment->created_at?->toIso8601String(),
            'images'      => $comment->ticketImageSources(),
            'attachments' => $comment->ticketAttachments('grp.media.download'),
        ];
    }
}
