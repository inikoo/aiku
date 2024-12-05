<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 29-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Helpers\AskLlama;

use App\Actions\Helpers\AskLlama\Traits\WithHelper;
use App\Actions\OrgAction;
use App\Http\Resources\Helpers\AskLlamaResource;
use App\Models\Helpers\Chunk;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Pgvector\Laravel\Distance;

class AskLlama extends OrgAction
{
    use AsController;
    use WithHelper;

    protected function getSiblings(Collection $results): Collection
    {
        $siblingsIncluded = collect();

        foreach ($results as $result) {
            $siblingsIncluded->push($result);

            $previousSibling = $this->getSiblingOrNot($result, $result->section_number - 1);
            if ($previousSibling) {
                $siblingsIncluded->push($previousSibling);
            }

            $nextSibling = $this->getSiblingOrNot($result, $result->section_number + 1);
            if ($nextSibling) {
                $siblingsIncluded->push($nextSibling);
            }
        }

        return $siblingsIncluded->unique('id');
    }

    protected function getSiblingOrNot(Chunk $result, int $sectionNumber): ?Chunk
    {
        return Chunk::query()
            ->where('sort_order', $result->sort_order)
            ->where('section_number', $sectionNumber)
            ->first();
    }

    public function promptTemplate($context, $question): string
    {
        return "
        **Role**
        You are a Chatbot operating in a Retrieval-Augmented Generation (RAG) system. Your responsibility is to generate an accurate response to the user's query based strictly on the provided context.

        **Instructions**
        - If the context does not contain enough information to answer the question, respond politely, for example: \"I'm sorry, but I don't have enough information to answer that question based on what I know.\"
        - Do not include the question or context in your response.
        - Avoid using phrases like \"Based on the provided context\" in your response.

        **CONTEXT (JSON)**
        {$context}
        
        **RESPONSE**
        Provide a direct, concise, and accurate answer using only the information in the context above.

        **QUESTION**
        {$question}

        ";
    }

    protected function calculateCosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            throw new InvalidArgumentException('Vectors must be of the same length.');
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += pow($vectorA[$i], 2);
            $magnitudeB += pow($vectorB[$i], 2);
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA * $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    public function handle($q): array
    {

        $embedQ = Ollama::model(config('ollama-laravel.embedding_model'))->embeddings($q);

        if (!isset($embedQ['embedding'])) {
            return ['error' => 'model not found', 'status' => 422];
        }

        $embeddingColumn = $this->get_embedding_size(config('llmdriver.driver'));
        $query = Chunk::query()
        ->orderBy('sort_order')
        ->orderBy('section_number')
        ->nearestNeighbors(
            $embeddingColumn,
            $embedQ['embedding'],
            Distance::Cosine
        )
        ->get()
        ->chunk(100, function ($chunk) use ($embedQ, $embeddingColumn) {
            return $chunk->filter(function ($item) use ($embedQ, $embeddingColumn) {
                try {
                    $embedding = json_decode($item->{$embeddingColumn}, true);
                    $score = round($this->calculateCosineSimilarity($embedding, $embedQ['embedding']), 3);
                } catch (\Exception $e) {
                    dd($e->getMessage());
                    return false;
                }
                return $score >= 0.56;
            });
        });

        $resultMerge = $query->collapse();
        $parentRes = collect($resultMerge)
            ->unique('id')
            ->take(4);

        $finalRes = $this->getSiblings($parentRes);

        $context = [];

        foreach ($finalRes as $res) {
            $context[] = [
                'content' => $res->content,
            ];
        }

        $prompt = $this->promptTemplate(json_encode($context), $q);

        $response = Ollama::prompt($prompt)
            ->model('llama3.2:3b')
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
        // dd('kena');

        if (isset($res['error'])) {
            return response()->json(AskLlamaResource::make($res), $res['status']);
        }


        return AskLlamaResource::make($res);
    }

    public $commandSignature = 'ask:llama {q}';

    public function asCommand($command): void
    {

        // generate chunk vector for all product
        // $product = Product::query()->orderBy('id')->get();

        // foreach ($product as $p) {
        //     $metadata = $p->toArray();
        //     $content = Arr::only($metadata, ['code', 'status', 'slug', 'units', 'unit' ,'name', 'price', 'description']);
        //     data_set($content, 'symbol_currency', Currency::find($metadata['currency_id'])->symbol);
        //     ChunkText::make()->handle(json_encode($content), $metadata);
        // }
        // dd('done');

        dd(AskLlamaResource::collection($this->handle($command->argument('q'))));
    }
}
