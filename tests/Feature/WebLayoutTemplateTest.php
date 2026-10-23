<?php

/*
 * Author Louis Perez
 * Created on 10-08-2026-14h-33m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Web\WebLayoutTemplate\ApplyWebLayoutTemplate;
use App\Actions\Web\Website\StoreWebsite;
use App\Enums\Web\Webpage\WebpageSubTypeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Web\WebLayoutTemplate;
use App\Models\Web\Website;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();

    actingAs($this->user);

    $this->website = $this->shop->website ?? StoreWebsite::make()->action(
        $this->shop,
        Website::factory()->definition()
    );
});

test('a storefront only lists storefront templates, even when every template is asked for, HELP-4146', function () {
    $storefront = $this->website->storefront;

    $template = WebLayoutTemplate::create([
        'name'     => 'Storefront template',
        'scope'    => 'Webpage',
        'type'     => $storefront->type->value,
        'sub_type' => $storefront->sub_type->value,
        'blocks'   => [
            ['type' => 'overview-1'],
            ['type' => 'gallery-1'],
        ],
    ]);

    WebLayoutTemplate::create([
        'name'     => 'Blog template',
        'scope'    => 'Webpage',
        'type'     => WebpageTypeEnum::BLOG->value,
        'sub_type' => WebpageSubTypeEnum::BLOG->value,
        'blocks'   => [],
    ]);

    $response = getJson(route('grp.json.template_layouts.index', ['webpage' => $storefront->id, 'filter' => ['show' => 'all']]));

    $response->assertOk();

    expect(collect($response->json('data'))->pluck('type')->unique()->values()->all())->toBe([WebpageTypeEnum::STOREFRONT->value]);

    $response = getJson(route('grp.json.template_layouts.index', ['webpage' => $storefront->id, 'filter' => ['show' => 'matching']]));

    $response->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($template->id)
        ->and($response->json('data.0.name'))->toBe('Storefront template')
        ->and($response->json('data.0.scope'))->toBe('Webpage')
        ->and($response->json('data.0.blocks_count'))->toBe(2);
});

test('index web layout templates returns empty when no template matches the webpage type', function () {
    $storefront = $this->website->storefront;

    WebLayoutTemplate::query()->delete();

    WebLayoutTemplate::create([
        'name'     => 'Blog only template',
        'scope'    => 'Webpage',
        'type'     => WebpageTypeEnum::BLOG->value,
        'sub_type' => WebpageSubTypeEnum::BLOG->value,
        'blocks'   => [],
    ]);

    $response = getJson(route('grp.json.template_layouts.index', ['webpage' => $storefront->id, 'filter' => ['show' => 'matching']]));

    $response->assertOk();

    expect($response->json('data'))->toBe([]);
});

test('a layout template is deleted only by its author or the group webmaster, INI-073', function () {
    $storefront = $this->website->storefront;
    $template   = fn (?int $authorId) => WebLayoutTemplate::create([
        'name'      => 'INI-073 '.uniqid(),
        'scope'     => 'Webpage',
        'type'      => $storefront->type->value,
        'sub_type'  => $storefront->sub_type->value,
        'blocks'    => [],
        'author_id' => $authorId,
    ]);
    $own      = $template($this->user->id);
    $someones = $template(null);

    $originalRoles       = $this->user->roles->pluck('name')->toArray();
    $originalPermissions = $this->user->getDirectPermissions()->pluck('name')->all();

    try {
        actingAsUserWithOnlyPermissions($this->user, ["web.{$this->shop->id}.edit"]);

        $listed = collect(getJson(route('grp.json.template_layouts.index', ['webpage' => $storefront->id]))->assertOk()->json('data'))->keyBy('id');
        expect($listed[$own->id]['can_delete'])->toBeTrue()
            ->and($listed[$someones->id]['can_delete'])->toBeFalse();

        Pest\Laravel\deleteJson(route('grp.models.web_layout_template.delete', $someones->id))->assertForbidden();
        expect(WebLayoutTemplate::find($someones->id))->not->toBeNull();

        Pest\Laravel\deleteJson(route('grp.models.web_layout_template.delete', $own->id))->assertSuccessful();
        expect(WebLayoutTemplate::find($own->id))->toBeNull();

        actingAsUserWithOnlyPermissions($this->user, ['group-webmaster.view']);
        Pest\Laravel\deleteJson(route('grp.models.web_layout_template.delete', $someones->id))->assertSuccessful();
        expect(WebLayoutTemplate::find($someones->id))->toBeNull();
    } finally {
        setPermissionsTeamId($this->user->group_id);
        $this->user->syncPermissions($originalPermissions);
        actingAsUserWithRoles($this->user, $originalRoles);
        \App\Actions\SysAdmin\User\SetUserAuthorisedModels::run($this->user);
    }
});

test('a storefront is saved as a template and takes only storefront templates; blogs and system pages take none, HELP-4146', function () {
    $storefront = $this->website->storefront;
    $name       = 'HELP-4146 storefront '.uniqid();
    $block      = ['ulid' => (string) Str::ulid(), 'show' => true, 'type' => 'overview-1', 'position' => 0, 'fieldValue' => ['text' => 'Hello'], 'visibility' => ['in' => true, 'out' => true]];

    postJson(route('grp.models.webpage.store_as_template', ['webpage' => $storefront->id]), ['name' => $name, 'blocks' => [$block]])->assertSuccessful();

    $saved = WebLayoutTemplate::where('name', $name)->firstOrFail();
    expect($saved->type)->toBe(WebpageTypeEnum::STOREFRONT->value);

    $contentTemplate = WebLayoutTemplate::create([
        'name'     => 'HELP-4146 content '.uniqid(),
        'scope'    => 'Webpage',
        'type'     => WebpageTypeEnum::CONTENT->value,
        'sub_type' => WebpageSubTypeEnum::CONTENT->value,
        'blocks'   => [$block],
    ]);

    postJson(route('grp.models.webpage.apply_template', ['webpage' => $storefront->id]), [
        'template_id' => $contentTemplate->id,
        'blocks'      => [['id' => null, 'ulid' => $block['ulid'], 'show' => true, 'type' => 'overview-1', 'visibility' => ['in' => true, 'out' => true], 'position' => 0]],
    ])->assertStatus(422);

    expect(ApplyWebLayoutTemplate::templateFits($storefront, $saved))->toBeTrue()
        ->and(ApplyWebLayoutTemplate::canUseTemplates($storefront))->toBeTrue()
        ->and(ApplyWebLayoutTemplate::canUseTemplates((clone $storefront)->forceFill(['type' => WebpageTypeEnum::BLOG])))->toBeFalse()
        ->and(ApplyWebLayoutTemplate::canUseTemplates((clone $storefront)->forceFill(['type' => WebpageTypeEnum::SYSTEM_PAGE])))->toBeFalse()
        ->and(ApplyWebLayoutTemplate::templateFits((clone $storefront)->forceFill(['type' => WebpageTypeEnum::CONTENT]), $saved))->toBeFalse()
        ->and(ApplyWebLayoutTemplate::templateFits((clone $storefront)->forceFill(['type' => WebpageTypeEnum::CONTENT]), $contentTemplate))->toBeTrue();

    $otherPage = $this->website->webpages()->where('type', '!=', WebpageTypeEnum::STOREFRONT->value)->firstOrFail();
    $listed    = collect(getJson(route('grp.json.template_layouts.index', ['webpage' => $otherPage->id, 'filter' => ['show' => 'all']]))->assertOk()->json('data'));
    expect($listed->pluck('type'))->not->toContain(WebpageTypeEnum::STOREFRONT->value);
});
