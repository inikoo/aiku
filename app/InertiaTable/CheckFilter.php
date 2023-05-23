<?php

namespace App\InertiaTable;

use Illuminate\Contracts\Support\Arrayable;

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

    public function toArray(): array
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
