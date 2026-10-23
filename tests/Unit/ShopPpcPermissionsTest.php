<?php

use App\Actions\Traits\Authorisations\WithShopPpcPermissions;
use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;

test('a web user or a guest can never edit seo', function () {
    $permissions = new class () {
        use WithShopPpcPermissions;
    };

    expect($permissions->canEditSeo(new WebUser(), new Shop()))->toBeFalse()
        ->and($permissions->canEditSeo(null, new Shop()))->toBeFalse();
});
