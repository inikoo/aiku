<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Catalogue\Shop\UpdateShop;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Helpers\Country;
use App\Models\Helpers\Language;

use function Pest\Laravel\actingAs;

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

test('update shop setting', function () {
    $c = Country::first();
    $l = Language::first();

    $modelData = [
        'company_name' => 'new company name',
        'code' => "NEW",
        'name' => "new_name",
        'type' => ShopTypeEnum::DROPSHIPPING,
        'country_id' => $c->id,
        'language_id' => $l->id,
        'email' => "test@gmail.com",
        'phone' => "08912312313"

    ];
    $shop = UpdateShop::make()->action($this->shop, $modelData);
    expect($shop)->toBeInstanceOf(Shop::class)
        ->and($shop->company_name)->toBe('new company name')
        ->and($shop->code)->toBe('NEW')
        ->and($shop->name)->toBe('new_name')
        ->and($shop->type)->toBe(ShopTypeEnum::DROPSHIPPING)
        ->and($shop->country_id)->toBe($c->id)
        ->and($shop->language_id)->toBe($l->id)
        ->and($shop->email)->toBe('test@gmail.com')
        ->and($shop->phone)->toBe('08912312313');
});
