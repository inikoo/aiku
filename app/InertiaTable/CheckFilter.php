<?php

namespace App\InertiaTable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use App\Enums\HumanRecources\Employee\EmployeeStateEnum;

class CheckFilter implements Arrayable
{
    public function __construct(
        public string $label,
        public array $options,
        public int $count,
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
            'count'    => $this->count,
            'value'     => $this->value,
            'checked'    => $this->checked,
        ];
    }
}
