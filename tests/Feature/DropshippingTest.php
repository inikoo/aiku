<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 16:30:57 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\CRM\Customer\AttachCustomerToPlatform;
use App\Actions\CRM\Customer\UpdateCustomerPlatform;
use App\Actions\CRM\CustomerClient\StoreCustomerClient;
use App\Actions\CRM\CustomerClient\UpdateCustomerClient;
use App\Actions\Dropshipping\Portfolio\AttachPortfolioToPlatform;
use App\Actions\Dropshipping\Portfolio\StorePortfolio;
use App\Actions\Dropshipping\Portfolio\UpdatePortfolio;
use App\Actions\Helpers\Images\GetPictureSources;
use App\Actions\Helpers\Media\SaveModelImages;
use App\Actions\SysAdmin\Group\CreateAccessToken;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Helpers\ImgProxy\Image;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\Platform;
use App\Models\Dropshipping\PlatformStats;
use App\Models\Dropshipping\Portfolio;
use App\Models\Helpers\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
    $this->user         = createAdminGuest($this->group)->user;


    $shop = Shop::first();
    if (!$shop) {
        $storeData = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::DROPSHIPPING);

        $shop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->shop = $shop;

    $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);

    $this->customer = createCustomer($this->shop);

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

test('test platform were seeded ', function () {
    expect($this->group->platforms()->count())->toBe(2);
    $platform = Platform::first();
    expect($platform)->toBeInstanceOf(Platform::class)
        ->and($platform->stats)->toBeInstanceOf(PlatformStats::class);

    $this->artisan('group:seed-platforms '.$this->group->slug)->assertExitCode(0);
    expect($this->group->platforms()->count())->toBe(2);
});

test('create customer client', function () {
    $customerClient = StoreCustomerClient::make()->action($this->customer, CustomerClient::factory()->definition());

    expect($customerClient)->toBeInstanceOf(CustomerClient::class);

    return $customerClient;
});

test('update customer client', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['reference' => '001']);
    expect($customerClient->reference)->toBe('001');
})->depends('create customer client');

test('add product to customer portfolio', function () {
    $dropshippingCustomerPortfolio = StorePortfolio::make()->action(
        $this->customer,
        [
            'product_id' => $this->product->id
        ]
    );
    expect($dropshippingCustomerPortfolio)->toBeInstanceOf(Portfolio::class);

    return $dropshippingCustomerPortfolio;
});

test('add platform to customer', function () {
    $platform = $this->group->platforms()->where('type', PlatformTypeEnum::SHOPIFY)->first();


    expect($this->customer->platforms->count())->toBe(0)
        ->and($this->customer->platform())->toBeNull();
    $customer = AttachCustomerToPlatform::make()->action(
        $this->customer,
        $platform,
        [
            'reference' => 'test_shopify_reference'
        ]
    );


    $customer->refresh();


    expect($customer->platforms->first())->toBeInstanceOf(Platform::class)
        ->and($customer->platform())->toBeInstanceOf(Platform::class)
        ->and($customer->platform()->type)->toBe(PlatformTypeEnum::SHOPIFY);


    return $customer;
});


test('add platform to portfolio', function (Portfolio $portfolio) {
    expect($portfolio->platforms->count())->toBe(0)
        ->and($portfolio->platform())->toBeNull();
    $portfolio = AttachPortfolioToPlatform::make()->action(
        $portfolio,
        [
            'reference' => 'test_shopify_reference_for_product'
        ]
    );


    $platformWithModelHasPlatformsPivotData = $portfolio->platforms()->first();

    expect($platformWithModelHasPlatformsPivotData)->toBeInstanceOf(Platform::class)
        ->and($platformWithModelHasPlatformsPivotData->pivot->reference)->toBe('test_shopify_reference_for_product')
        ->and($portfolio->platform())->toBeInstanceOf(Platform::class)
        ->and($portfolio->platform()->type)->toBe(PlatformTypeEnum::SHOPIFY);
})->depends('add product to customer portfolio');


test('change customer platform from shopify to tiktok', function (Customer $customer) {
    expect($customer->platforms->count())->toBe(1)
        ->and($customer->platform()->type)->toBe(PlatformTypeEnum::SHOPIFY);
    $customer = UpdateCustomerPlatform::make()->action(
        $customer,
        Platform::where('type', PlatformTypeEnum::TIKTOK->value)->first(),
        [
            'reference' => 'test_update_platform_to_tiktok'
        ]
    );

    expect($customer->platforms()->first())->toBeInstanceOf(Platform::class)
        ->and($customer->platform()->type)->toBe(PlatformTypeEnum::TIKTOK);
})->depends('add platform to customer');


test('add image to product', function () {
    Storage::fake('public');

    expect($this->product)->toBeInstanceOf(Product::class)
        ->and($this->product->images->count())->toBe(0);

    $fakeImage = UploadedFile::fake()->image('hello.jpg');
    $path      = $fakeImage->store('photos', 'public');

    SaveModelImages::run(
        $this->product,
        [
            'path'         => Storage::disk('public')->path($path),
            'originalName' => $fakeImage->getClientOriginalName()

        ],
        'photo',
        'product_images'
    );

    $this->product->refresh();

    expect($this->product)->toBeInstanceOf(Product::class)
        ->and($this->product->images->count())->toBe(1);
});

test('add 2nd image to product', function () {
    Storage::fake('public');

    $fakeImage2 = UploadedFile::fake()->image('hello2.jpg', 20, 20);


    $path2 = $fakeImage2->store('photos', 'public');

    SaveModelImages::run(
        $this->product,
        [
            'path'         => Storage::disk('public')->path($path2),
            'originalName' => $fakeImage2->getClientOriginalName()
        ],
        'photo',
        'product_images'
    );

    $this->product->refresh();

    expect($this->product)->toBeInstanceOf(Product::class)
        ->and($this->product->images->count())->toBe(2);
});

test('get product 1s1 images', function () {
    $media1 = $this->product->images->first();
    expect($media1)->toBeInstanceOf(Media::class);

    $image = $media1->getImage();
    expect($image)->toBeInstanceOf(Image::class);

    $imageSources1 = GetPictureSources::run($image);

    expect($imageSources1)->toBeArray()->toHaveCount(3);
})->depends('add 2nd image to product');

test('get product 2nd images and show resized sources', function () {
    $media2 = $this->product->images->last();
    expect($media2)->toBeInstanceOf(Media::class);


    $image2 = $media2->getImage()->resize(5, 5);
    expect($image2)->toBeInstanceOf(Image::class);

    $imageSources2 = GetPictureSources::run($image2);
    expect($imageSources2)->toBeArray()->toHaveCount(6);
})->depends('add 2nd image to product');


test('update customer portfolio', function (Portfolio $dropshippingCustomerPortfolio) {
    $dropshippingCustomerPortfolio = UpdatePortfolio::make()->action(
        $dropshippingCustomerPortfolio,
        [
            'reference' => 'new_reference'
        ]
    );
    expect($dropshippingCustomerPortfolio->reference)->toBe('new_reference');

    return $dropshippingCustomerPortfolio;
})->depends('add product to customer portfolio');


test('get dropshipping access token', function () {
    $token = CreateAccessToken::make()->action($this->group, ['name' => 'test_token', 'abilities' => ['bk-api']]);
    expect($token)->toBeString();
    $this->token = $token;
})->skip();
