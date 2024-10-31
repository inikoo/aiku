<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);

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
