<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Nov 2024 15:22:46 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Comms\Mailshot\StoreMailshot;
use App\Actions\CRM\BackInStockReminder\DeleteBackInStockReminder;
use App\Actions\CRM\BackInStockReminder\StoreBackInStockReminder;
use App\Actions\CRM\BackInStockReminder\UpdateBackInStockReminder;
use App\Actions\CRM\Customer\AddDeliveryAddressToCustomer;
use App\Actions\CRM\Customer\DeleteCustomerDeliveryAddress;
use App\Actions\CRM\Customer\HydrateCustomers;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\CustomerNote\StoreCustomerNote;
use App\Actions\CRM\CustomerNote\UpdateCustomerNote;
use App\Actions\CRM\Favourite\StoreFavourite;
use App\Actions\CRM\Favourite\UpdateFavourite;
use App\Actions\CRM\Poll\StorePoll;
use App\Actions\CRM\Poll\UpdatePoll;
use App\Actions\CRM\PollOption\StorePollOption;
use App\Actions\CRM\Prospect\StoreProspect;
use App\Actions\CRM\Prospect\Tags\SyncTagsProspect;
use App\Actions\CRM\Prospect\UpdateProspect;
use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\CRM\Poll\PollTypeEnum;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use App\Models\CRM\Customer;
use App\Models\CRM\CustomerNote;
use App\Models\CRM\Favourite;
use App\Models\CRM\Poll;
use App\Models\CRM\PollOption;
use App\Models\CRM\Prospect;
use App\Models\Helpers\Country;
use App\Models\Helpers\Query;
use App\Models\Reminder\BackInStockReminder;
use Illuminate\Support\Carbon;
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

    list(
        $this->tradeUnit,
        $this->product
    ) = createProduct($this->shop);

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
    expect(Query::where('model', 'Prospect')->count())->toBe(2);
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

    /** @var Prospect $prospect */
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
        ->and(Query::where('slug', 'prospects-last-contacted-within-interval')->first()->number_items)->toBe(2);
});

test('create prospect mailshot', function () {
    $shop         = $this->shop;
    $outbox       = Outbox::where('shop_id', $shop->id)->where('code', OutboxCodeEnum::INVITE)->first();
    expect($outbox)->toBeInstanceOf(Outbox::class);
    $dataModel    = [
        'subject'    => 'hello',
        'type'       => MailshotTypeEnum::INVITE,
        'state'      => MailshotStateEnum::IN_PROCESS,
        'recipients_recipe' => []

    ];
    $mailshot     = StoreMailshot::make()->action($outbox, $dataModel);
    expect($mailshot)->toBeInstanceOf(Mailshot::class)
        ->and($outbox->stats->number_mailshots)->toBe(1);
});

test('add delivery address to customer', function (Customer $customer) {
    $country = Country::latest()->first();
    $customer = AddDeliveryAddressToCustomer::make()->action(
        $customer,
        [
                    'delivery_address' => [
                        'address_line_1'      => fake()->streetAddress,
                        'address_line_2'      => fake()->buildingNumber,
                        'sorting_code'        => '',
                        'postal_code'         => fake()->postcode,
                        'locality'            => fake()->city,
                        'dependent_locality'  => '',
                        'administrative_area' => '',
                        'country_id'          => $country->id
                    ]
                ]
    );

    expect($customer)->toBeInstanceOf(Customer::class)
    ->and($customer->addresses->count())->toBe(2);

    return $customer;
})->depends('create customer');

test('remove delivery address from customer', function (Customer $customer) {
    $address = $customer->addresses()->skip(1)->first();
    $customer = DeleteCustomerDeliveryAddress::make()->action($customer, $address);

    expect($customer)->toBeInstanceOf(Customer::class)
    ->and($customer->addresses->count())->toBe(1);

    return $customer;
})->depends('add delivery address to customer');

test('can show list of prospects lists', function () {
    $shop     = $this->shop;
    $response = get(route('grp.org.shops.show.crm.prospects.lists.index', [$shop->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CRM/Prospects/Queries')
            ->has('title');
    });
})->todo();

test('add favourite to customer', function (Customer $customer) {
    $favourite = StoreFavourite::make()->action(
        $customer,
        $this->product,
        []
    );

    $customer->refresh();

    expect($favourite)->toBeInstanceOf(Favourite::class);

    expect($customer)->toBeInstanceOf(Customer::class)
    ->and($customer->favourites)->not->toBeNull()
    ->and($customer->favourites->count())->toBe(1);

    return $favourite;
})->depends('create customer');

test('update favourite', function (Favourite $favourite) {
    $targetDate = Carbon::now()->addDays(2)->startOfMinute();

    $updatedFavourite = UpdateFavourite::make()->action(
        favourite: $favourite,
        modelData: [
            'last_fetched_at' => $targetDate
        ],
        strict: false
    );

    $updatedFavourite->refresh();

    expect($updatedFavourite)->toBeInstanceOf(Favourite::class);
    // ->and($updatedFavourite->last_fetched_at)->toEqual($targetDate); //:(

    return $updatedFavourite;
})->depends('add favourite to customer');

test('add back in stock reminder to customer', function (Customer $customer) {
    $reminder = StoreBackInStockReminder::make()->action(
        $customer,
        $this->product,
        []
    );

    $customer->refresh();

    expect($reminder)->toBeInstanceOf(BackInStockReminder::class);

    expect($customer)->toBeInstanceOf(Customer::class)
    ->and($customer->backInStockReminder)->not->toBeNull()
    ->and($customer->backInStockReminder->count())->toBe(1);

    return $reminder;
})->depends('create customer');

test('update back in stock reminder', function (BackInStockReminder $reminder) {
    $targetDate = Carbon::now()->addDays(2)->startOfMinute();

    $updatedReminder = UpdateBackInStockReminder::make()->action(
        backInStockReminder: $reminder,
        modelData: [
            'last_fetched_at' => $targetDate
        ],
        strict: false
    );

    $updatedReminder->refresh();

    expect($updatedReminder)->toBeInstanceOf(BackInStockReminder::class);
    // ->and($updatedFavourite->last_fetched_at)->toEqual($targetDate); //:(

    return $updatedReminder;
})->depends('add back in stock reminder to customer');

test('delete back in stock reminder', function (BackInStockReminder $reminder) {
    $deletedReminder = DeleteBackInStockReminder::make()->action(
        backInStockReminder: $reminder,
    );

    expect(BackInStockReminder::find($deletedReminder->id))->toBeNull();

    return $deletedReminder;
})->depends('update back in stock reminder');

test('create customer note', function (Customer $customer) {
    expect($customer)->toBeInstanceOf(Customer::class)
    ->and($customer->customerNotes)->not->toBeNull()
    ->and($customer->customerNotes->count())->toBe(1);

    $note = StoreCustomerNote::make()->action(
        $customer,
        [
            'note' => 'note babadeebabadoo'
        ]
    );

    $customer->refresh();

    expect($note)->toBeInstanceOf(CustomerNote::class);

    expect($customer)->toBeInstanceOf(Customer::class)
    ->and($customer->customerNotes)->not->toBeNull()
    ->and($customer->customerNotes->count())->toBe(2);

    return $note;
})->depends('create customer');

test('update customer note', function (CustomerNote $note) {

    $updatedNote = UpdateCustomerNote::make()->action(
        $note,
        [
            'note' => 'note babadeebabadoo update'
        ]
    );

    $updatedNote->refresh();

    expect($updatedNote)->toBeInstanceOf(CustomerNote::class);

    return $updatedNote;
})->depends('create customer note');

test('hydrate customers', function (Customer $customer) {
    HydrateCustomers::run($customer);
    $this->artisan('hydrate:customers')->assertExitCode(0);
})->depends('create customer');

test('store poll', function () {

    $poll = StorePoll::make()->action(
        $this->shop,
        [
            'name' => 'namee',
            'label' => 'name',
            'in_registration' => false,
            'in_registration_required' => true,
            'in_iris' => true,
            'in_iris_required' => true,
            'type'  => PollTypeEnum::OPTION
        ]
    );

    $poll->refresh();

    expect($poll)->toBeInstanceOf(Poll::class)
        ->and($poll->name)->toBe('namee')
        ->and($poll->label)->toBe('name')
        ->and($poll->in_registration)->toBe(false)
        ->and($poll->in_registration_required)->toBe(true)
        ->and($poll->in_iris)->toBe(true)
        ->and($poll->in_iris_required)->toBe(true)
        ->and($poll->type)->toBe(PollTypeEnum::OPTION);

    return $poll;
});

test('update poll', function (Poll $poll) {

    $poll = UpdatePoll::make()->action(
        $poll,
        [
            'name' => 'optionss',
            'label' => 'some option',
            'in_registration' => true,
            'in_registration_required' => false,
            'in_iris' => false,
            'in_iris_required' => false,
        ]
    );

    $poll->refresh();

    expect($poll)->toBeInstanceOf(Poll::class)
        ->and($poll->name)->toBe('optionss')
        ->and($poll->label)->toBe('some option')
        ->and($poll->in_registration)->toBe(true)
        ->and($poll->in_registration_required)->toBe(false)
        ->and($poll->in_iris)->toBe(false)
        ->and($poll->in_iris_required)->toBe(false);

    return $poll;
})->depends('store poll');

test('store poll option', function (Poll $poll) {

    $pollOption = StorePollOption::make()->action(
        $poll,
        [
            'value' => 'value1',
            'label' => '1',
        ]
    );

    $pollOption->refresh();
    $poll->refresh();

    expect($pollOption)->toBeInstanceOf(PollOption::class)
        ->and($pollOption->value)->toBe('value1')
        ->and($pollOption->label)->toBe('1');

    expect($poll)->toBeInstanceOf(Poll::class)
        ->and($poll->pollOptions->count())->toBe(1);

    return $pollOption;
})->depends('update poll');
