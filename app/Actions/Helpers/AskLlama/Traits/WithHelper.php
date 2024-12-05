<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 03-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Helpers\AskLlama\Traits;

trait WithHelper
{
    public function driverHelper(string $driver, string $key): string
    {
        return config("llmdriver.drivers.{$driver}.{$key}");
    }

    public function get_embedding_size(string $embedding_driver): string
    {
        $embeddingModel = $this->driverHelper($embedding_driver, 'models.embedding_model');

        $size = config('llmdriver.embedding_sizes.'.$embeddingModel);

        if ($size) {
            return 'embedding_'.$size;
        }

        return 'embedding_3072';
    }
}
