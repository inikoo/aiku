<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-16h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentComment;
use App\Models\SysAdmin\User;
use App\Enums\SysAdmin\User\UserNotificationEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreProductDevelopmentComment extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ?ProductDevelopment $productDevelopment = null;

    private function target(): ProductDevelopment
    {
        return $this->productDevelopment ?? request()->route('productDevelopment');
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canViewProductDevelopment($request->user(), $this->target());
    }

    public function handle(ProductDevelopment $productDevelopment, User $author, array $modelData): ProductDevelopmentComment
    {
        $body      = trim((string) Arr::get($modelData, 'body', ''));
        $images    = Arr::get($modelData, 'images', []);
        $mentioned = $this->mentionedUsers($productDevelopment, $body, $author);

        return DB::transaction(function () use ($productDevelopment, $author, $modelData, $body, $mentioned, $images) {
            /** @var ProductDevelopmentComment $comment */
            $comment = $productDevelopment->comments()->create([
                'user_id'   => $author->id,
                'parent_id' => Arr::get($modelData, 'parent_id'),
                'body'      => $body,
                'mentions'  => $mentioned->pluck('id')->values()->all(),
            ]);

            $comment->attachTicketImages($images);

            if ($mentioned->isNotEmpty()) {
                NotifyProductDevelopmentUsers::run(
                    $productDevelopment,
                    $mentioned,
                    UserNotificationEnum::PRODUCT_DEVELOPMENT_MENTION,
                    __(':name mentioned you on :code', ['name' => $author->contact_name ?: $author->username, 'code' => $productDevelopment->code]),
                    Str::limit($body, 140),
                );
            }

            return $comment;
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function mentionedUsers(ProductDevelopment $productDevelopment, string $body, User $author)
    {
        preg_match_all('/@([A-Za-z0-9._-]+)/', $body, $matches);
        $usernames = collect($matches[1] ?? [])->map(fn (string $username) => rtrim($username, '.'))->unique()->values();

        if ($usernames->isEmpty()) {
            return collect();
        }

        $access = $this->productDevelopmentAccess();

        return User::where('group_id', $productDevelopment->group_id)
            ->where('status', true)
            ->whereIn('username', $usernames)
            ->where('id', '!=', $author->id)
            ->get()
            ->filter(fn (User $user) => $access->canView($user, $productDevelopment))
            ->values();
    }

    public function rules(): array
    {
        return [
            'body'      => ['required_without:images', 'nullable', 'string', 'max:5000'],
            'images'    => ['sometimes', 'array', 'max:5'],
            'images.*'  => ProductDevelopmentComment::ticketFileRules(),
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('product_development_comments', 'id')->where('product_development_id', $this->target()->id)],
        ];
    }

    public function getValidationMessages(): array
    {
        return ProductDevelopmentComment::ticketFileValidationMessages();
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopmentComment
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user(), $this->validatedData);
    }

    public function action(ProductDevelopment $productDevelopment, User $author, array $modelData): ProductDevelopmentComment
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $author, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
