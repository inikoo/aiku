<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Models\Web\Redirect;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

/**
 * Redirect tools are gated by can_use_mcp_web on the user's account, on top of the website edit
 * permission the same change needs in the UI. They only re-point redirects that already exist, to
 * a live page of the same website, through UpdateRedirect so caches and Varnish are purged as in the UI.
 */
abstract class AikuWebsiteRedirectsTool extends Tool
{
    use WithMcpPermissions;

    public const int MAX_REDIRECTS = 25;

    protected function rowRules(): array
    {
        return [
            'website'          => ['required', 'string'],
            'redirects'        => ['required', 'array', 'min:1', 'max:'.self::MAX_REDIRECTS],
            'redirects.*.from' => ['required', 'string', 'max:2048'],
            'redirects.*.to'   => ['present', 'nullable', 'string', 'max:2048'],
        ];
    }

    protected function denied(Request $request): ?Response
    {
        if ($request->user()?->can_use_mcp_web) {
            return null;
        }

        return Response::error('Changing website content is not enabled for this user. Do not retry; an administrator enrols it on the user\'s edit page.');
    }

    protected function editableWebsite(Request $request): Website|Response
    {
        $identifier = strtolower(trim((string) $request->string('website')));
        $domain     = Str::of($identifier)->after('://')->before('/')->replaceStart('www.', '')->toString();

        $website = Website::where('group_id', $request->user()->group_id)
            ->where(fn ($query) => $query->whereRaw('lower(slug) = ?', [$identifier])
                ->orWhereRaw('lower(domain) = ?', [$domain])
                ->orWhereHas('shop', fn ($query) => $query->whereRaw('lower(code) = ?', [$identifier])->orWhereRaw('lower(slug) = ?', [$identifier])))
            ->first();

        if ($website && $this->canEdit($request, $website)) {
            return $website;
        }

        $options = Website::where('group_id', $request->user()->group_id)->with('shop:id,code,type')->orderBy('id')->get()
            ->filter(fn (Website $website) => $this->canEdit($request, $website))
            ->map(fn (Website $website) => $website->slug.' ('.$website->domain.')');

        if ($options->isEmpty()) {
            return Response::error('You cannot edit any website.');
        }

        return Response::error("'{$request->string('website')}' is not a website you can edit. Use one of: {$options->implode(', ')}.");
    }

    private function canEdit(Request $request, Website $website): bool
    {
        $shop        = $website->shop;
        $permissions = ['group-webmaster.view', "supervisor-web.{$shop->id}", "web.{$shop->id}.edit"];
        if ($shop->type === ShopTypeEnum::FULFILMENT && $shop->fulfilment) {
            $permissions[] = "supervisor-fulfilment-shop.{$shop->fulfilment->id}";
            $permissions[] = "fulfilment-shop.{$shop->fulfilment->id}.edit";
        }

        return collect($permissions)->contains(fn (string $permission) => $this->userCan($request, $permission));
    }

    /**
     * @return array<int, array{from: string, to: string, redirect: ?Redirect, current: ?Webpage, target: ?Webpage, status: string}>
     */
    protected function resolveRows(Website $website, array $rows): array
    {
        $seenRedirectIds = [];

        return collect($rows)->map(function (array $row) use ($website, &$seenRedirectIds) {
            $redirect = $this->redirectFor($website, $row['from']);
            $targets  = $this->liveWebpagesFor($website, (string) $row['to']);
            $target   = $targets->count() === 1 ? $targets->first() : null;
            $current  = $redirect ? Webpage::find($redirect->to_webpage_id) : null;

            $status = match (true) {
                !$redirect                                       => 'error: no redirect for this path on '.$website->domain.'; only existing redirects can be changed here',
                in_array($redirect->id, $seenRedirectIds)        => 'error: this redirect is listed twice',
                $targets->count() > 1                            => 'error: several live pages answer to this target ('.$targets->pluck('canonical_url')->implode(', ').'); give its full url',
                !$target                                         => 'error: the new target is not a live page of '.$website->domain,
                $this->isLive($website, $redirect->from_path)    => 'skipped: the old page is live again, so visitors see it and this redirect is not used',
                $current?->id === $target->id                    => 'unchanged',
                default                                          => 'change',
            };
            if ($redirect) {
                $seenRedirectIds[] = $redirect->id;
            }

            return [
                'from'     => $row['from'],
                'to'       => (string) $row['to'],
                'redirect' => $redirect,
                'current'  => $current,
                'target'   => $target,
                'status'   => $status,
            ];
        })->all();
    }

    protected function describeRow(array $row): array
    {
        return [
            'from'        => $row['from'],
            'redirect_id' => $row['redirect']?->id,
            'from_url'    => $row['redirect']?->from_url,
            'current_to'  => $row['current'] ? ['url' => $row['current']->canonical_url, 'title' => $row['current']->title, 'type' => $row['current']->sub_type?->value] : null,
            'new_to'      => $row['target'] ? ['url' => $row['target']->canonical_url, 'title' => $row['target']->title, 'type' => $row['target']->sub_type?->value] : null,
            'status'      => $row['status'],
        ];
    }

    private function redirectFor(Website $website, string $from): ?Redirect
    {
        foreach ($this->pathCandidates($website, $from) as $path) {
            $redirect = Redirect::where('website_id', $website->id)->where('from_path', $path)->first();
            if ($redirect) {
                return $redirect;
            }
        }

        return null;
    }

    private function isLive(Website $website, string $path): bool
    {
        return Webpage::where('website_id', $website->id)->where('state', WebpageStateEnum::LIVE)->where('url', $path)->exists();
    }

    /**
     * Webpage urls are only unique per live page in practice, not by constraint: when two live pages
     * share the last segment, the full canonical path decides between them.
     *
     * @return Collection<int, Webpage>
     */
    private function liveWebpagesFor(Website $website, string $to): Collection
    {
        foreach ($this->pathCandidates($website, $to) as $path) {
            $webpages = Webpage::where('website_id', $website->id)
                ->where('state', WebpageStateEnum::LIVE)
                ->where('url', $path)
                ->get();
            if ($webpages->count() > 1) {
                $fullPath = $this->pathCandidates($website, $to)[0];
                $exact    = $webpages->filter(fn (Webpage $webpage) => trim((string) parse_url((string) $webpage->canonical_url, PHP_URL_PATH), '/') === $fullPath);

                return $exact->count() === 1 ? $exact->values() : $webpages;
            }
            if ($webpages->isNotEmpty()) {
                return $webpages;
            }
        }

        return new Collection();
    }

    /**
     * A webpage url is its own slug while canonical urls carry the parents
     * (/packaging/glass-bottles/gbot/gbot-01n), so the full path is tried first, then its last segment.
     * Any host is dropped (old urls may be on another subdomain); a bare domain is the home page, whose url is empty.
     *
     * @return array<int, string>
     */
    private function pathCandidates(Website $website, string $value): array
    {
        $value  = strtolower(Str::of(trim($value))->before('?')->before('#')->toString());
        $domain = strtolower($website->domain);
        $host   = Str::before(Str::after($value, '://'), '/');
        if (str_contains($value, '://') || $host === $domain || str_ends_with($host, '.'.$domain)) {
            $value = Str::after($value, '://');
            $value = str_contains($value, '/') ? Str::after($value, '/') : '';
        }
        $path = trim(rawurldecode($value), '/');

        return array_values(array_unique([$path, Str::afterLast($path, '/')]));
    }
}
