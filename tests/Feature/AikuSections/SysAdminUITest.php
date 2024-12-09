<?php

/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Analytics\GetSectionRoute;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Models\Analytics\AikuScopedSection;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);
    $this->artisan('group:seed_aiku_scoped_sections')->assertExitCode(0);
    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
});

test('UI show organisation setting', function () {

    $response = get(
        route(
            'grp.org.settings.edit',
            [
                $this->organisation->slug,
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has('formData.blueprint.0.fields', 2)
            ->has('formData.blueprint.1.fields', 1)
            ->has('formData.blueprint.2.fields', 4)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.org.update')
                        ->where('parameters', [$this->organisation->id])
            );
    });
})->todo();

test('UI index organisation', function () {
    $response = get(
        route(
            'grp.organisations.index',
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Organisations/Organisations')
            ->where('title', 'organisations')
            ->has('breadcrumbs', 2)
            ->has('data')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'organisations')
                        ->etc()
            );
    });
});

test('UI edit organisation', function () {
    $this->withoutExceptionHandling();
    $response = get(
        route(
            'grp.organisations.edit',
            [$this->organisation->slug]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->where('title', 'organisation')
            ->has('breadcrumbs', 3)
            ->has('formData')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->organisation->name)
                        ->etc()
            );
    });
});

test('UI organisation edit settings', function () {
    $response = get(
        route(
            'grp.org.settings.edit',
            [$this->organisation->slug]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->where('title', 'Organisation settings')
            ->has('breadcrumbs', 2)
            ->has('formData')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Organisation settings')
                        ->etc()
            );
    });
});

test('UI get section route group sysadmin index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.sysadmin.dashboard', []);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::GROUP_SYSADMIN->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->group->slug);
});

test('UI get section route group dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.dashboard', []);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::GROUP_DASHBOARD->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->group->slug);
});

test('UI get section route group goods dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.goods.dashboard', []);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::GROUP_GOODS->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->group->slug);
});

test('UI get section route group organisation dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.organisations.index', []);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::GROUP_ORGANISATION->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->group->slug);
});

test('UI get section route group profile dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.profile.showcase.show', []);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::GROUP_PROFILE->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->group->slug);
});

test('UI get section route org dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.dashboard.show', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_DASHBOARD->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});

test('UI get section route org setting edit', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.settings.edit', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_SETTINGS->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});

// other section org

test('UI get section route org reports index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.reports.index', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_REPORT->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});

test('UI get section route org shops index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.shops.index', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_SHOP->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});
