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

function guard_running()
{
    return is_file(guard_pidfile());
}

function guard_get_pids()
{
    return file_get_contents(guard_pidfile());
}

function guard_pidfile()
{
    return GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'.pidfile';
}