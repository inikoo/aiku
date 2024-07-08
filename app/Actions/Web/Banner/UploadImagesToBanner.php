<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Portfolio\Banner;

use App\Actions\CRM\Customer\AttachImageToCustomer;
use App\Enums\Portfolio\Banner\BannerStateEnum;
use App\Http\Resources\Gallery\ImageResource;
use App\Models\Portfolio\Banner;
use App\Models\Portfolio\PortfolioWebsite;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

use function Sentry\captureException;

class UploadImagesToBanner
{
    use AsAction;
    use WithAttributes;


    private PortfolioWebsite|null $portfolioWebsite = null;


    public function handle(Banner $banner, array $imageFiles): Collection
    {
        $medias = [];
        foreach ($imageFiles as $imageFile) {
            $media = AttachImageToCustomer::run(
                customer: customer(),
                collection: 'content_block',
                imagePath: $imageFile->getPathName(),
                originalFilename: $imageFile->getClientOriginalName(),
                extension: $imageFile->guessClientExtension()
            );


            $medias[] = $media;
            $scope    = 'unpublished-slide';
            $count    = $banner->images()->wherePivot('scope', $scope)->count();

            if ($count == 0) {
                $banner->images()->attach(
                    $media->id,
                    [
                        'scope' => $scope
                    ]
                );

                if ($banner->state == BannerStateEnum::UNPUBLISHED) {
                    $banner->update(
                        [
                            'data->unpublished_image_id' => $media->id
                        ]
                    );
                }
            }
        }

        return collect($medias);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    public function rules(): array
    {
        return [
            'images'   => ['required'],
            'images.*' => ["mimes:jpg,png,jpeg,gif,mp4","max:50000"]
        ];
    }


    public function asController(Banner $banner, ActionRequest $request): Collection
    {
        try {
            $request->validate();
        } catch (Exception $e) {
            captureException($e);
        }
        return $this->handle($banner, $request->validated('images'));
    }



    public function jsonResponse($medias): AnonymousResourceCollection
    {
        return ImageResource::collection($medias);
    }

}
