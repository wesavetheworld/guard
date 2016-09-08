<?php namespace Avram\Guard;

class FileEvent implements \JsonSerializable
{
    protected $id, $siteName, $path, $type, $attempts, $status, $firstAttempt, $lastAttempt;

    const BLOCKED = 'BLOCKED';
    const NOTIFIED = 'NOTIFIED';

    public function __construct($siteName, $path, $type, $attempts, $status = self::BLOCKED)
    {
        $this->siteName = $siteName;
        $this->path     = $path;
        $this->type     = $type;
        $this->attempts = (int)$attempts;
        $this->status   = $status;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getAttempts()
    {
        return (int)$this->attempts;
    }

    public function increaseAttemptsCounter()
    {
        $this->attempts += 1;
    }

    /**
     * @return mixed
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param mixed $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    /**
     * @return mixed
     */
    public function getFirstAttempt()
    {
        return $this->firstAttempt;
    }

    /**
     * @param mixed $firstAttempt
     */
    public function setFirstAttempt($firstAttempt)
    {
        $this->firstAttempt = $firstAttempt;
    }

    /**
     * @return mixed
     */
    public function getLastAttempt()
    {
        return $this->lastAttempt;
    }

    /**
     * @param mixed $lastAttempt
     */
    public function setLastAttempt($lastAttempt)
    {
        $this->lastAttempt = $lastAttempt;
    }


    public static function block($path, $event, $site)
    {

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
        $obj                = new \stdClass();
        $obj->site          = $this->getSiteName();
        $obj->path          = $this->getPath();
        $obj->type          = $this->getType();
        $obj->attempts      = $this->getAttempts();
        $obj->status        = $this->getStatus();
        $obj->first_attempt = $this->getFirstAttempt();
        $obj->last_attempt  = $this->getLastAttempt();
        return $obj;
    }

    public static function fromJSON($json)
    {
        $obj = new FileEvent($json->site, $json->path, $json->type, $json->attempts, $json->status);
        return $obj;
    }
}