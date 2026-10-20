<?php

namespace App\Actions\Iris\Reviews;

use App\Actions\IrisAction;
use App\Models\Ordering\Order;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowIrisOrderReview extends IrisAction
{
    use WithIrisOrderReviewAccess;

    /**
     * @return array<string, mixed>
     */
    public function handle(Order $order): array
    {
        return $this->visitorOrderReview($order);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(Order $order, string $token, ActionRequest $request): array
    {
        $this->initialisation($request);
        $this->ensureReviewLink($order, $token);

        return $this->handle($order);
    }

    /**
     * @param  array<string, mixed>  $order
     */
    public function htmlResponse(array $order, ActionRequest $request): Response
    {
        return Inertia::render('OrderReview', [
            'title'      => __('Review your order'),
            'order'      => $order,
            'preselect'  => $this->preselected($request->query()),
        ]);
    }
}
