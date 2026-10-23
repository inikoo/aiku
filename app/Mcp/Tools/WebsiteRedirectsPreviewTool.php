<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Before changing where redirects of a website point, shows for each old url its existing redirect, where it points now and the live page it would point to instead, with status change, unchanged, skipped (the old page is live again) or error. Only existing redirects (closed pages) can be re-pointed, and only to a live page of the same website. from and to take full urls or paths; an empty to is the home page. Up to 25 per call; split longer lists. Read only; show the result to the user and pass the same rows to website-redirects-tool once they confirmed. Only for users enrolled to change website content through their assistant.')]
#[IsReadOnly]
class WebsiteRedirectsPreviewTool extends AikuWebsiteRedirectsTool
{
    public function handle(Request $request): Response
    {
        $request->validate($this->rowRules());

        $denied = $this->denied($request);
        if ($denied) {
            return $denied;
        }

        $website = $this->editableWebsite($request);
        if ($website instanceof Response) {
            return $website;
        }

        $rows = collect($this->resolveRows($website, $request->get('redirects')));

        return Response::json([
            'website'   => $website->domain,
            'changes'   => $rows->where('status', 'change')->count(),
            'unchanged' => $rows->filter(fn (array $row) => in_array(Str::before($row['status'], ':'), ['unchanged', 'skipped']))->count(),
            'errors'    => $rows->filter(fn (array $row) => str_starts_with($row['status'], 'error'))->count(),
            'rows'      => $rows->map(fn (array $row) => $this->describeRow($row))->values()->all(),
        ]);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'website'   => $schema->string()->description('Website slug or domain, or its shop code, e.g. aroma or aw-aromatics.com')->required(),
            'redirects' => $schema->array()->items($schema->object([
                'from' => $schema->string()->description('Old url or path that is redirected now')->required(),
                'to'   => $schema->string()->description('Url or path of the live page it should go to; empty for the home page')->required(),
            ]))->description('Up to 25 rows')->required(),
        ];
    }
}
