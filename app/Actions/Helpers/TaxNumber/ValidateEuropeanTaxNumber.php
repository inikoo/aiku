<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 Mar 2023 01:55:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TaxNumber;

use App\Enums\Helpers\TaxNumber\TaxNumberStatusEnum;
use App\Enums\Helpers\TaxNumber\TaxNumberTypeEnum;
use App\Models\Helpers\TaxNumber;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use phpDocumentor\Reflection\Exception;
use SoapClient;
use SoapFault;

class ValidateEuropeanTaxNumber
{
    use AsAction;

    public function __construct(int $timeout = 10)
    {
        $this->timeout = $timeout;
    }

    public const URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    private ?SoapClient $client = null;

    protected int $timeout;

    /**
     * @throws \SoapFault
     */
    protected function getClient(): SoapClient
    {
        if ($this->client === null) {
            $this->client = new SoapClient(self::URL, ['connection_timeout' => $this->timeout]);
        }

        return $this->client;
    }

    /**
     * @throws \phpDocumentor\Reflection\Exception
     */
    public function handle(TaxNumber $taxNumber): TaxNumber
    {
        if ($taxNumber->type == TaxNumberTypeEnum::EU_VAT) {
            try {
                $response = $this->getClient()->checkVat(
                    array(
                        'countryCode' => $taxNumber->country_code,
                        'vatNumber'   => $taxNumber->number
                    )
                );

                $validationDate = gmdate('Y-m-d H:i:s');
                $validationData = [
                    'valid'      => $response->valid,
                    'status'     => $response->valid ? TaxNumberStatusEnum::VALID : TaxNumberStatusEnum::INVALID,
                    'checked_at' => $validationDate
                ];
                if (!$response->valid) {
                    $validationData['invalid_checked_at'] = $validationDate;
                } else {
                    $validationData['data'] = [
                        'name'    => $response->name,
                        'address' => $response->address,

                    ];
                }


                $taxNumber->update($validationData);
            } catch (SoapFault $e) {
                $validationDate = gmdate('Y-m-d H:i:s');


                if (!preg_match('/INVALID_INPUT/i', $e->getMessage())) {
                    $validationData = [
                        'valid'              => false,
                        'status'             => TaxNumberStatusEnum::INVALID,
                        'checked_at'         => $validationDate,
                        'invalid_checked_at' => $validationDate

                    ];
                } else {
                    $validationData = [
                        'external_service_failed_at' => gmdate('Y-m-d H:i:s'),
                        'data'                       => [
                            'exception' => [
                                'code'    => $e->getCode(),
                                'message' => Str::limit($e->getMessage(), 4000)
                            ]
                        ]
                    ];
                }

                $taxNumber->update($validationData);

                throw new Exception($e->getMessage(), $e->getCode());
            }
        }


        return $taxNumber;
    }
}
