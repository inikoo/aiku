<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Procurement\OrgAgent\StoreOrgAgent;
use App\Actions\Procurement\OrgPartner\StoreOrgPartner;
use App\Actions\Procurement\OrgSupplier\StoreOrgSupplier;
use App\Actions\SupplyChain\Agent\StoreAgent;
use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses()->group('ui');

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);

    $otherOrg = Organisation::skip(1)->take(1)->first();
    if(!$otherOrg){
        $orgData = Organisation::factory()->definition();
        data_set($orgData, 'code', 'acmo');
        data_set($orgData, 'type', OrganisationTypeEnum::SHOP);
        $otherOrg = StoreOrganisation::make()->action($this->organisation->group, $orgData);
    }

    $this->otherOrg = $otherOrg;

    $agent = Agent::first();
    if (!$agent) {
        $storeData = Agent::factory()->definition();
        $agent     = StoreAgent::make()->action(
            $this->organisation->group,
            $storeData
        );
    }

    $this->agent = $agent;

    $orgAgent = OrgAgent::first();
    if (!$orgAgent) {
        $orgAgent     = StoreOrgAgent::make()->action(
            $this->organisation,
            $this->agent,
            []
        );
    }

    $this->orgAgent = $orgAgent;

    
    $supplier = Supplier::first();
    if (!$supplier) {
        $storeData = Supplier::factory()->definition();
        $supplier  = StoreSupplier::make()->action(
            $this->agent,
            $storeData
        );
    }

    $this->supplier = $supplier;

    $orgSupplier = OrgSupplier::first();
    if (!$orgSupplier) {
        $orgSupplier = StoreOrgSupplier::make()->action(
            $this->organisation,
            $this->supplier,
        );
    }

    $this->orgSupplier = $orgSupplier;

    $orgPartner = OrgPartner::first();
    if (!$orgPartner) {
        $orgPartner = StoreOrgPartner::make()->action(
            $this->organisation,
            $this->otherOrganisation,
        );
    }

    $this->orgPartner = $orgPartner;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
});

test('UI Index org suppliers', function () {

    $this->withoutExceptionHandling();
    $response = $this->get(route('grp.org.procurement.org_suppliers.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Procurement/OrgSuppliers')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});

test('UI show org supplier', function () {
    // dd($this->orgSupplier);
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.procurement.org_suppliers.show', [$this->organisation->slug, $this->orgSupplier->id]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Procurement/OrgSupplier')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgSupplier->name)
                        ->etc()
            )
            ->has('tabs');

    });
})->todo();

test('UI Index org agents', function () {
    $response = $this->get(route('grp.org.procurement.org_agents.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Procurement/OrgAgents')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});

test('UI show org agents', function () {
    $response = $this->get(route('grp.org.procurement.org_agents.show', [$this->organisation->slug, $this->orgAgent->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Procurement/OrgAgent')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgAgent->agent->organisation->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI Index purchase orders', function () {
    $response = $this->get(route('grp.org.procurement.purchase_orders.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Procurement/PurchaseOrders')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});

test('UI Index org partners', function () {
    $response = $this->get(route('grp.org.procurement.org_partners.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Procurement/Partners')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});

test('UI show org partners', function () {
    $response = $this->get(route('grp.org.procurement.org_partners.show', [$this->organisation->slug, $this->orgPartner->id]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Procurement/Partner')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgPartner->partner->name)
                        ->etc()
            )
            ->has('tabs');

    });
});