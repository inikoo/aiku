<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 29-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Http\Resources\Helpers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $model_type
 * @property mixed $result
 * @property mixed $model_id
 */
class AskLlamaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'model' => $this->model_type,
            'created_at' => $this->created_at,
            'response' => $this->response,
            'done' => $this->done,
            'done_reason' => $this->done_reason,
            'context' => $this->context,
            'total_duration' => $this->total_duration,
            'load_duration' => $this->load_duration,
            'prompt_eval_count' => $this->prompt_eval_count,
            'prompt_eval_duration' => $this->prompt_eval_duration,
            'eval_count' => $this->eval_count,
            'eval_duration' => $this->eval_duration,
        ];
    }

}
