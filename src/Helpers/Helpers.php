<?php

function glob2arr($pattern)
{
    return explode(';', $pattern);
}

function fnmmatch($pattern, $string, $flags = null)
{
    $patterns = glob2arr($pattern);

    foreach ($patterns as $pat) {
        if (fnmatch($pat, $string, $flags)) {
            return true;
        }
    }

    return false;
}