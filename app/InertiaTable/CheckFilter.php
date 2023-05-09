<?php

namespace App\InertiaTable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use App\Enums\HumanRecources\Employee\EmployeeStateEnum;

class CheckFilter implements Arrayable
{
    public function __construct(
        public string $key,
        public string $label,
        public int $count,
        public ?string $value = null,
        public bool $checked,
    ) {
    }

    public function toArray()
    {

        return [
            'key'    => $this->key,
            'label'  => $this->label,
            'count'  => $this->count,
            'value'  => $this->value,
            'checked'=> $this->checked,
        ];
    }
}
