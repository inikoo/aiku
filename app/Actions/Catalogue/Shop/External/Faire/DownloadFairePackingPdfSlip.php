<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 04 Apr 2026 13:05:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\External\Faire;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Models\Ordering\Order;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Decorators\JobDecorator;

class DownloadFairePackingPdfSlip extends OrgAction implements ShouldBeUnique
{
    public string $commandSignature = 'faire:packing-slip-download {order}';


    public string $jobQueue = 'low-priority';

    public int $jobTries = 3;
    public int $jobBackoff = 3600;


    public function getJobUniqueId(?int $orderId): string
    {
        return $orderId ?? 'empty';
    }


    /**
     * @throws \Exception
     */
    public function handle(?int $orderId, bool $isLastAttempt = true): void
    {

        if (!$orderId) {
            return;
        }
        $order = Order::find($orderId);
        if (!$order) {
            return;
        }


        $pdfBinary = GetFairePackingPdfSlip::run($order);

        if (!is_string($pdfBinary)) {
            if (!$isLastAttempt) {
                throw new \Exception('Faire has no packing slip PDF yet for order '.$order->slug);
            }

            \Sentry\withScope(function ($scope) use ($order, $pdfBinary) {
                $scope->setContext('faire_packing_slip', [
                    'order'       => $order->slug,
                    'external_id' => $order->external_id,
                    'error'       => $pdfBinary['error'] ?? null,
                ]);
                \Sentry\captureMessage('Faire refused the packing slip PDF for order '.$order->slug);
            });

            return;
        }


        $tempPath = tempnam(sys_get_temp_dir(), 'packing-slip-');
        file_put_contents($tempPath, $pdfBinary);

        $attachmentData = [
            'path' => $tempPath,
            'originalName' => 'packing-slip-' . $order->slug . '.pdf',
            'scope' => 'packing-slip',
            'caption' => 'Packing Slip for Order ' . $order->slug,
            'extension' => 'pdf'
        ];

        SaveModelAttachment::make()->action($order, $attachmentData);

        // Clean up temporary file
        @unlink($tempPath);

    }


    /**
     * Faire can be slow to produce the slip, so a refusal is retried and only the last one is
     * reported, without failing the job.
     *
     * @throws \Exception
     */
    public function asJob(JobDecorator $job, ?int $orderId): void
    {
        $this->handle($orderId, $job->attempts() >= $this->jobTries);
    }

    /**
     * @throws \Exception
     */
    public function asCommand(Command $command): void
    {
        $order = Order::where('slug', $command->argument('order'))->firstOrFail();
        $this->handle($order->id);
    }
}
