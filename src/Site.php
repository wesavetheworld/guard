<?php namespace Avram\Guard;

class Site implements \JsonSerializable
{
    /** @var string */
    protected $name, $path, $types, $email;
    /** @var array */
    protected $excludes;

    public function __construct($name, $path = ".", $types = "*.php;*.htm*;*.js;*.css;*.sql", $email = null, $excludes = null)
    {
        $this->setName($name);
        $this->setPath(realpath($path));
        $this->setTypes($types);
        $this->setEmail($email);
        $this->excludes = [];

        foreach ($excludes as $excludedPath) {
            $this->addExclude(is_file($excludedPath) ? realpath($excludedPath) : $excludedPath);
        }

    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return array
     */
    public function getExcludes()
    {
        return $this->excludes;
    }

    /**
     * @param array $excludes
     */
    public function setExcludes(array $excludes)
    {
        $this->excludes = $excludes;
    }

    /**
     * @param $path
     */
    public function addExclude($path)
    {
        $excludes   = $this->getExcludes();
        $excludes[] = realpath($path);
        $this->setExcludes($excludes);
    }

    /**
     * @param $path
     */
    public function removeExclude($path)
    {
        $paths = array_filter($this->getExcludes(), function ($elem) use ($path) {
            return ($elem != $path);
        });

        $this->setExcludes($paths);
    }


    /**
     * @param null|string $fileName
     *
     * @return string
     */
    public function backupPath($fileName = null)
    {
        $path = GUARD_USER_FOLDER.DIRECTORY_SEPARATOR."backups".DIRECTORY_SEPARATOR.$this->getName();

        if ($fileName) {
            $path .= DIRECTORY_SEPARATOR.$fileName;
        }

        return $path;
    }

    /**
     * @param null|string $fileName
     *
     * @return string
     */
    public function quarantinePath($fileName = null)
    {
        $path = GUARD_USER_FOLDER.DIRECTORY_SEPARATOR."quarantine".DIRECTORY_SEPARATOR.$this->getName();

        if ($fileName) {
            $path .= DIRECTORY_SEPARATOR.$fileName;
        }

        return $path;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        $obj           = new \stdClass();
        $obj->name     = $this->getName();
        $obj->path     = $this->getPath();
        $obj->email    = $this->getEmail();
        $obj->types    = $this->getTypes();
        $obj->excludes = $this->getExcludes();
        return $obj;
    }

    /**
     * @param $jsonObject
     *
     * @return static
     * @throws \Exception
     */
    public static function fromJSONObject($jsonObject)
    {
        if (!is_object($jsonObject)) {
            throw new \Exception("JSON malformed: $jsonObject");
        }

        if (empty($jsonObject->name)) {
            throw new \Exception("Name missing: $jsonObject");
        }

        $object = new static($jsonObject->name);

        if (isset($jsonObject->path)) {
            $object->setPath($jsonObject->path);
        }

        if (isset($jsonObject->email)) {
            $object->setEmail($jsonObject->email);
        }

        if (isset($jsonObject->types)) {
            $object->setTypes($jsonObject->types);
        }

        if (isset($jsonObject->excludes) && is_array($jsonObject->excludes)) {
            $object->setExcludes($jsonObject->excludes);
        }

        return $object;
    }

    /**
     * @param $jsonString
     *
     * @return Site
     * @throws \Exception
     */
    public static function fromJSONString($jsonString)
    {
        return static::fromJSONObject(json_decode($jsonString));
    }
}