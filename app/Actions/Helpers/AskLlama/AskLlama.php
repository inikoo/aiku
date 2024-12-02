<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 29-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Helpers\AskLlama;

use App\Actions\OrgAction;
use App\Http\Resources\Helpers\AskLlamaResource;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Ollama;

class AskLlama extends OrgAction
{
    use AsController;

    public function handle($q): array
    {
        $response = Ollama::prompt($q)
            ->model('llama3.2:11b')
            ->options(['temperature' => 0.8])
            ->stream(false)
            ->ask();
        if (isset($response['error'])) {
            data_set($response, 'status', 422);
            data_set($response, 'error', 'model not found');
        }
        if (!$response) {
            data_set($response, 'status', 503);
            data_set($response, 'error', 'model not setup yet');
        }

        return $response;
    }

    public function asController(ActionRequest $request): AskLlamaResource|JsonResponse
    {
        $res = $this->handle($request->input('q'));

        if (isset($res['error'])) {
            return response()->json(AskLlamaResource::make($res), $res['status']);
        }

        return AskLlamaResource::make($res);
    }

    public $commandSignature = 'ask:llama {q}';

    public function asCommand($command): void
    {
        dd(AskLlamaResource::collection($this->handle($command->argument('q'))));
    }
}
