<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 11:18:49 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\Customer\HydrateCustomers;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Prospect\StoreProspect;
use App\Actions\CRM\Prospect\Tags\SyncTagsProspect;
use App\Actions\CRM\Prospect\UpdateProspect;
use App\Actions\Mail\Mailshot\StoreMailshot;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\Helpers\Query;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);

});


test('create customer', function () {
    $customer = StoreCustomer::make()->action(
        $this->shop,
        Customer::factory()->definition(),
    );
    $customer->refresh();

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED)
        ->and($customer->group->crmStats->number_customers)->toBe(1)
        ->and($customer->organisation->crmStats->number_customers)->toBe(1)
        ->and($customer->shop->crmStats->number_customers)->toBe(1)
    ;

    return $customer;
});

test('create other customer', function () {
    try {
        $customer = StoreCustomer::make()->action(
            $this->shop,
            Customer::factory()->definition()
        );
    } catch (Throwable) {
        $customer = null;
    }
    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000002')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return $customer;
});




test('prospect queries are seeded', function () {
    $this->artisan('query:seed-prospects')->assertExitCode(0);
    expect(Query::where('model_type', 'Prospect')->count())->toBe(2);
});

test('create prospect', function () {
    $shop      = $this->shop;
    $modelData = Prospect::factory()->definition();
    data_set($modelData, 'tags', ['seo', 'tag 1', ' hello ', 'Seo']);
    $prospect = StoreProspect::make()->action($shop, $modelData);
    expect($prospect)->toBeInstanceOf(Prospect::class)
        ->and($shop->crmStats->number_prospects)->toBe(1)
        ->and($shop->crmStats->number_prospects_state_no_contacted)->toBe(1)
        ->and($shop->crmStats->number_prospects_state_contacted)->toBe(0)
        ->and($shop->crmStats->number_prospects_state_fail)->toBe(0)
        ->and($shop->crmStats->number_prospects_state_success)->toBe(0)
        ->and($this->organisation->crmStats->number_prospects)->toBe(1)
        ->and($this->organisation->crmStats->number_prospects_state_no_contacted)->toBe(1)
        ->and($this->organisation->crmStats->number_prospects_state_contacted)->toBe(0)
        ->and($this->organisation->crmStats->number_prospects_state_fail)->toBe(0)
        ->and($this->organisation->crmStats->number_prospects_state_success)->toBe(0);

    $this->assertDatabaseCount('tags', 3);

    return $prospect;
});

test('update prospect', function () {

    $prospect  = Prospect::first();
    $modelData = [
        'contact_name' => 'new name',
    ];
    $prospect = UpdateProspect::make()->action(prospect: $prospect, modelData: $modelData);
    expect($prospect)->toBeInstanceOf(Prospect::class)->and($prospect->contact_name)->toBe('new name');
    return $prospect;
});

test('create 2nd prospect', function () {
    $shop         = $this->shop;
    $organisation = $this->organisation;
    $modelData    = Prospect::factory()->definition();
    $prospect     = StoreProspect::make()->action($shop, $modelData);
    expect($prospect)->toBeInstanceOf(Prospect::class)
        ->and($shop->crmStats->number_prospects)->toBe(2)
        ->and($shop->crmStats->number_prospects_state_no_contacted)->toBe(2)
        ->and($shop->crmStats->number_prospects_state_contacted)->toBe(0)
        ->and($shop->crmStats->number_prospects_state_fail)->toBe(0)
        ->and($shop->crmStats->number_prospects_state_success)->toBe(0)
        ->and($organisation->crmStats->number_prospects)->toBe(2)
        ->and($organisation->crmStats->number_prospects_state_no_contacted)->toBe(2)
        ->and($organisation->crmStats->number_prospects_state_contacted)->toBe(0)
        ->and($organisation->crmStats->number_prospects_state_fail)->toBe(0)
        ->and($organisation->crmStats->number_prospects_state_success)->toBe(0);

    return $prospect;
});

test('update prospect tags', function ($prospect) {

    $modelData = [
        'tags' => ['seo', 'social'],
    ];
    $prospect = SyncTagsProspect::make()->action(prospect: $prospect, modelData: $modelData);
    expect($prospect)->toBeInstanceOf(Prospect::class)->and($prospect->tags->count())->toBe(2);
    return $prospect;
})->depends('create 2nd prospect');

test('prospect query count', function () {
    $this->artisan('query:count')->assertExitCode(0);
    expect(Query::where('slug', 'prospects-not-contacted')->first()->number_items)->toBe(2)
        ->and(Query::where('slug', 'prospects-last-contacted')->first()->number_items)->toBe(2);
});

test('create prospect mailshot', function () {
    $shop         = $this->shop;
    $organisation = $this->organisation;
    $dataModel    = [
        'subject'    => 'hello',
        'type'       => MailshotTypeEnum::PROSPECT_MAILSHOT,
        'outbox_id'  => Outbox::where('shop_id', $shop->id)->where('type', OutboxTypeEnum::SHOP_PROSPECT)->pluck('id')->first(),
        'recipients' => []

    ];
    $mailshot     = StoreMailshot::make()->action($shop, $dataModel);
    expect($mailshot)->toBeInstanceOf(Mailshot::class)
        ->and($organisation->mailStats->number_mailshots)->toBe(1)
        ->and($organisation->mailStats->number_mailshots_type_prospect_mailshot)->toBe(1)
        ->and($organisation->mailStats->number_mailshots_state_in_process)->toBe(1)
        ->and($organisation->mailStats->number_mailshots_type_prospect_mailshot_state_in_process)->toBe(1)
        ->and($shop->mailStats->number_mailshots)->toBe(1)
        ->and($shop->mailStats->number_mailshots_type_prospect_mailshot)->toBe(1)
        ->and($shop->mailStats->number_mailshots_state_in_process)->toBe(1)
        ->and($shop->mailStats->number_mailshots_type_prospect_mailshot_state_in_process)->toBe(1);
})->todo();

test('can show list of prospects', function () {
    $shop     = $this->shop;
    $response = get(route('grp.org.shops.show.crm.prospects.index', [$shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CRM/Prospects')
            ->has('title');
    });
})->todo();

test('can show list of mailshots', function () {
    $shop     = $this->shop;
    $response = get(route('grp.org.shops.show.crm.prospects.mailshots.index', [$shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CRM/Prospects/Mailshots')
            ->has('title');
    });
})->todo();

test('can show list of prospects lists', function () {
    $shop     = $this->shop;
    $response = get(route('grp.org.shops.show.crm.prospects.lists.index', [$shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CRM/Prospects/Queries')
            ->has('title');
    });
})->todo();

test('can show list of tags', function () {
    $shop     = $this->shop;
    $response = get(route('grp.org.shops.show.crm.prospects.tags.index', [$shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CRM/Prospects/Tags')
            ->has('title');
    });
})->todo();

test('hydrate customers', function (Customer $customer) {
    HydrateCustomers::run($customer);
    $this->artisan('hydrate:customers')->assertExitCode(0);
})->depends('create customer');
