<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 20:23:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Utils;

use Illuminate\Database\Eloquent\Model;

class ActionResult
{
    public array $changes;
    public array $errors;
    public Model|null $model;
    public string $action;

    public int|null $model_id;
    public array $data;
    public string $message;

    public function __construct()
    {
        $this->changes = [];
        $this->errors  = [];
        $this->message = '';

        $this->status = 'unchanged';

        $this->model    = null;
        $this->model_id = null;
        $this->data     = [];
    }
}
