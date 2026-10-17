<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use App\Actions\Web\Redirect\UpdateRedirect;
use App\Enums\SysAdmin\McpChange\McpChangeTypeEnum;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Re-points existing redirects of a website to other live pages, after website-redirects-preview-tool has been shown to the user and they confirmed in their own words. Pass the same rows as the preview; when any row has an error nothing is changed. Rows already pointing to that page, or whose old page is live again, are skipped. Up to 25 per call. Every change is kept in the AI changes log with the request text and can be reverted there. Only for users enrolled to change website content through their assistant.')]
class WebsiteRedirectsTool extends AikuWebsiteRedirectsTool
{
    use WithMcpChangeLog;

    public function handle(Request $request): Response
    {
        $request->validate(array_merge($this->rowRules(), [
            'request_text' => ['required', 'string', 'max:4000'],
        ]));

        $denied = $this->denied($request);
        if ($denied) {
            return $denied;
        }

        $website = $this->editableWebsite($request);
        if ($website instanceof Response) {
            return $website;
        }

        $rows   = collect($this->resolveRows($website, $request->get('redirects')));
        $errors = $rows->filter(fn (array $row) => str_starts_with($row['status'], 'error'));
        if ($errors->isNotEmpty()) {
            return Response::error('Nothing was changed. '.$errors->map(fn (array $row) => $row['from'].': '.$row['status'])->implode('; '));
        }

        $changes = $rows->where('status', 'change');
        if ($changes->isNotEmpty()) {
            $this->recordChange(
                $request,
                McpChangeTypeEnum::WEBSITE_REDIRECTS,
                $changes->count().' redirects on '.$website->domain,
                ['redirect_ids' => $changes->map(fn (array $row) => $row['redirect']->id)->values()->all()],
                fn () => DB::transaction(fn () => $changes->each(
                    fn (array $row) => UpdateRedirect::make()->action($row['redirect'], ['to_webpage_id' => $row['target']->id])
                ))
            );
        }

        return Response::json([
            'website'       => $website->domain,
            'changed'       => $changes->count(),
            'unchanged'     => $rows->count() - $changes->count(),
            'change_log_id' => $this->mcpChange?->id,
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'website'      => $schema->string()->description('Website slug or domain, or its shop code, as in the preview')->required(),
            'redirects'    => $schema->array()->items($schema->object([
                'from' => $schema->string()->description('Old url or path that is redirected now')->required(),
                'to'   => $schema->string()->description('Url or path of the live page it should go to; empty for the home page')->required(),
            ]))->description('The rows the user confirmed in the preview, up to 25')->required(),
            'request_text' => $schema->string()->description('The user\'s request, verbatim, kept in the audit')->required(),
        ];
    }
}
