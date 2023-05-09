<?php

namespace App\InertiaTable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use App\Enums\HumanRecources\Employee\EmployeeStateEnum;

class CheckOptions implements Arrayable
{
    public function __construct(
        public string $label,
        public array $options,
        public array $totals,
        public ?string $value = null,
        public bool $checked,
    ) {
    }

    public function toArray()
    {
        $options = $this->options;

        if ($this->show) {
            $options = EmployeeStateEnum;
        }

        return [
            'label'     => $this->label,
            'options'   => $this->options,
            'totals'    => $this->totals,
            'value'     => $this->value,
            'checked'    => $this->checked,
        ];
    }
}
