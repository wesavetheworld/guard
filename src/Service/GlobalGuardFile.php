<?php namespace Avram\Guard\Service;

class GlobalGuardFile extends GuardFile
{
    public function setPaths(array $paths)
    {
        $this->data->paths = $paths;
    }

    public function getPaths()
    {
        return isset($this->data->paths) ? $this->data->paths : [];
    }

    public function addPath($path)
    {
        $paths = $this->getPaths();
        $paths[] = realpath($path);
        $this->setPaths($paths);
    }

    public function removePath($path)
    {
        $paths = array_filter($this->getPaths(), function($elem) use ($path) {
            return ($elem != $path);
        });

        $this->setPaths($paths);
    }
}