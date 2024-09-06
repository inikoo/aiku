<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\CurrencyExchange;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\CurrencyExchange as CurrencyExchangeModel;

class UpdateCurrencyExchange
{
    use WithActionUpdate;

    public function handle(CurrencyExchangeModel $currencyExchange, array $modelData): CurrencyExchangeModel
    {
        return $this->update($currencyExchange, $modelData);
    }

    public function rules(): array
    {
        return [
            'currency' => ['sometimes', 'required'],
        ];
    }



}
