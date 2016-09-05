<?php namespace Avram\Guard\Service;


use Avram\Guard\Exceptions\GuardFileException;

class GuardFile
{
    /** @var string */
    protected $fileName;
    /** @var \SplFileInfo */
    protected $file;
    /** @var \stdClass */
    protected $data;

    public function __construct($fileName = './.guardfile')
    {
        $this->fileName = $fileName;
        $this->file     = new \SplFileInfo($fileName);
        $this->data     = new \stdClass();

        if (!$this->exists()) {
            throw new GuardFileException("File {$fileName} does not exist or is not readable! Use: php guard.phar init");
        }

        $contents = file_get_contents($this->file->getPathname());
        if (empty($contents)) {
            throw new GuardFileException("File {$fileName} is empty! Delete it and use: php guard.phar init");
        }

        $this->data = json_decode($contents);
        if (!is_object($this->data)) {
            throw new GuardFileException("File {$fileName} is wrongly formatted! Delete it and use: php guard.phar init");
        }

    }

    public function exists()
    {
        return ($this->file->isFile() && $this->file->isReadable());
    }

    public function setPath($path)
    {
        $this->data->path = realpath($path);
    }

    public function getPath()
    {
        return isset($this->data->path) ? $this->data->path : '.';
    }

    public function setExtensions($exts)
    {
        $this->data->extensions = $exts;
    }

    public function getExtensions()
    {
        return isset($this->data->extensions) ? $this->data->extensions : '*';
    }

    public function getExtensionsArray()
    {
        return explode(';', $this->getExtensions());
    }

    public function setExcludes(array $excludes)
    {
        $this->data->excludes = $excludes;
    }

    public function getExcludes()
    {
        return isset($this->data->excludes) ? $this->data->excludes : [];
    }

    public function addExclude($path)
    {
        $excludes   = $this->getExcludes();
        $excludes[] = realpath($path);
        $this->setExcludes($excludes);
    }

    public function removePath($path)
    {
        $paths = array_filter($this->getExcludes(), function ($elem) use ($path) {
            return ($elem != $path);
        });

        $this->setExcludes($paths);
    }

    public function data()
    {
        return $this->data;
    }

    public function writeFile()
    {
        file_put_contents($this->file->getPathname(), $this->data, JSON_PRETTY_PRINT);
    }
}