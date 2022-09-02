<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:35:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions;

use App\Models\Utils\ActionResult;
use Illuminate\Support\Arr;

/**
 * @property \Illuminate\Database\Eloquent\Model $model
 * @property array $modelData
 */
class UpdateModelAction
{

    /**
     * @var \App\Models\Utils\ActionResult
     */
    private ActionResult $res;

    function __construct()
    {
        $this->res = new ActionResult();
    }

    protected function extractJson($field): array
    {
        $data = [];
        foreach (Arr::dot(Arr::only($this->modelData, $field)) as $key => $value) {
            if (is_array($value)) {
                if (count($value) == 0) {
                    $value = null;
                } else {
                    $value = json_encode($value);
                }
            }
            if ($value) {
                $data[preg_replace('/\./', '->', $key)] = $value;
            }
        }

        return $data;
    }

    protected function update($jsonFields=[]): void
    {

            $this->model->update(Arr::except($this->modelData,$jsonFields));
            $this->model->update($this->extractJson($jsonFields));

    }

    protected function updateAndFinalise($jsonFields=[]): ActionResult
    {

        $this->update($jsonFields);
        return $this->finalise();
    }

    protected function finalise(): ActionResult
    {
        $this->res->changes  = array_merge($this->res->changes, $this->model->getChanges());
        $this->res->model    = $this->model;
        $this->res->model_id = $this->model->id;
        $this->res->status   = $this->res->changes ? 'updated' : 'unchanged';

        return $this->res;
    }

}

