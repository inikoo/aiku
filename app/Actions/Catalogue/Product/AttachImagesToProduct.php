<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 15:50:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\WithUploadProductImage;
use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Http\Resources\Helpers\ImageResource;
use App\Models\Catalogue\Product;
use App\Models\Helpers\Media;
use App\Models\SysAdmin\Organisation;
use Google\Service\ShoppingContent\ActionReason;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class AttachImagesToProduct extends OrgAction
{
    use HasWebAuthorisation;
    use WithAttachMediaToModel;

    public function handle(Product $product, $scope, array $modelData): Product
    {
        foreach ($modelData['images'] as $image) {
            $media = Media::find($image);
            $this->attachMediaToModel($product, $media, $scope);
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'images'   => ['required', 'array'],
        ];
    }

    public function jsonResponse($medias): AnonymousResourceCollection
    {
        return ImageResource::collection($medias);
    }

    public function asController(Organisation $organisation, Product $product, ActionRequest $request)
    {
        $this->initialisation($organisation, $request);

        return $this->handle($product, 'image', $this->validatedData);
    }
}
