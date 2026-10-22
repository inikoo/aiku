<?php

/*
 * Author: Andi Ferdiawan <andiferdiawan22@gmail.com>
 * Created: Wed, 21 Oct 2026
 * Copyright (c) 2026, Andi Ferdiawan
 */

namespace App\Exceptions;

use Exception;

/**
 * Not a RuntimeException on purpose: the Meta commands read a RuntimeException as one metric or
 * field set Meta refused and carry on with the next, where this has to stop the whole run.
 */
class MetaUsageLimitReached extends Exception
{
    public static function make(int $percent, ?int $minutesToWait = null, ?string $limit = null): static
    {
        $replace = ['percent' => $percent, 'minutes' => $minutesToWait, 'limit' => $limit ?? __('usage')];

        return new static($minutesToWait
            ? __('Meta usage limit nearly reached (:limit :percent%), stopped to avoid a block. Try again in about :minutes minutes.', $replace)
            : __('Meta usage limit nearly reached (:limit :percent%), stopped to avoid a block. Try again later.', $replace));
    }

    public static function throttled(?int $minutesToWait = null): static
    {
        return new static($minutesToWait
            ? __('Meta usage limit reached: Meta asked to slow down, stopped to avoid a block. Try again in about :minutes minutes.', ['minutes' => $minutesToWait])
            : __('Meta usage limit reached: Meta asked to slow down, stopped to avoid a block. Try again later.'));
    }
}
