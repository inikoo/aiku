<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Reports\MontanaInvoices\ExportMontanaInvoices;
use App\Actions\Accounting\Reports\SageInvoices\ExportSageInvoices;
use App\Models\SysAdmin\Organisation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Symfony\Component\HttpFoundation\StreamedResponse;

test('invoice report downloads include invoices from the whole last day of the range', function (string $action) {
    $captured = new class () {
        public ?FromQuery $export = null;
    };

    $spy = Mockery::mock($action)->makePartial();
    $spy->shouldReceive('streamMappedCsv')->once()->andReturnUsing(function (FromQuery $export) use ($captured) {
        $captured->export = $export;

        return new StreamedResponse();
    });

    $spy->handle((new Organisation())->forceFill(['id' => 0]), ['type' => 'csv', 'between' => ['date' => '20260901-20260930']]);

    expect($captured->export->query()->getBindings())->toContain('2026-09-01 00:00:00', '2026-09-30 23:59:59');
})->with([ExportSageInvoices::class, ExportMontanaInvoices::class]);
