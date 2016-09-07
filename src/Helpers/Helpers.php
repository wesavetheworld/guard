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

function rmdir_recursive($dir)
{
    $files = scandir($dir);
    array_shift($files);    // remove '.' from array
    array_shift($files);    // remove '..' from array

    foreach ($files as $file) {
        $file = $dir.'/'.$file;
        if (is_dir($file)) {
            @rmdir_recursive($file);
            @rmdir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dir);
}