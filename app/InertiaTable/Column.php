<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 May 2023 16:39:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\InertiaTable;

use Illuminate\Contracts\Support\Arrayable;

class Column implements Arrayable
{
    public function __construct(
        public string $key,
        public string|array $label,
        public bool $canBeHidden,
        public bool $hidden,
        public bool $sortable,
        public bool|string $sorted,
        public ?string $type,
        public ?string $className,
    ) {
    }

    public function toArray(): array
    {
        return [
            'key'                => $this->key,
            'label'              => $this->label,
            'can_be_hidden'      => $this->canBeHidden,
            'hidden'             => $this->hidden,
            'sortable'           => $this->sortable,
            'sorted'             => $this->sorted,
            'type'               => $this->type,
            'className'          => $this->className,
        ];
    }
}
