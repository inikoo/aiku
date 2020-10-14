<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 15 Oct 2020 00:39:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


if (! function_exists('au_escape_slug')) {
    function au_escape_slug($sluggableName)
    {
        $sluggableName = preg_replace('/\'/', '', $sluggableName);

        $sluggableName = preg_replace('/www\./', 'www ', $sluggableName);
        $sluggableName = preg_replace('/\.com/', ' com', $sluggableName);
        $sluggableName = preg_replace('/&/', ' and ', $sluggableName);

        $sluggableName = preg_replace('/\./', '', $sluggableName);

        $sluggableName = preg_replace('/-/', ' ', $sluggableName);
        return  trim($sluggableName);
    }
}
