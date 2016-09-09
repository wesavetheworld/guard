<?php namespace Avram\Guard\Services;

use Avram\Guard\Exceptions\EventsFileException;
use Avram\Guard\FileEvent;

class EventsFile
{
    /** @var string */
    protected $fileName;
    /** @var \SplFileInfo */
    protected $file;
    /** @var \stdClass */
    protected $data;

    public function __construct($fileName = null)
    {
        if (empty($fileName)) {
            $fileName = GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'events.json';
        }

        $this->fileName = $fileName;
        $this->file     = new \SplFileInfo($fileName);
        $json           = [];

        if ($this->file->isFile() && $this->file->isReadable()) {
            $contents = file_get_contents($this->file->getPathname());
            if (!empty($contents)) {
                $json = json_decode($contents);
            }

            if (!is_array($json)) {
                throw new EventsFileException("File {$fileName} is wrongly formatted!");
            }
        }

        $this->data = $json;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        $result = [];
        foreach ($this->data as $ev) {
            $result[] = new FileEvent($ev->path, $ev->type, $ev->attempts, $ev->status, $ev->first_attempt, $ev->last_attempt);
        }

        return $result;
    }

    public function setEvents(array $events)
    {
        $this->data = $events;
    }

    public function getFileEventIndexByPath($path)
    {
        for ($i = 0; $i < count($this->data); $i++) {
            if ($this->data[$i]->path == $path) {
                return $i;
            }
        }

        return false;
    }

    public function getFileEventByPath($path)
    {
        $index = $this->getFileEventIndexByPath($path);
        if ($index === false) {
            return false;
        }

        return FileEvent::fromJSONObject($this->data[$index]);
    }

    public function updateFileEvent(FileEvent $event, $position = null)
    {
        if ($position === null) {
            $index = $this->getFileEventIndexByPath($event->getPath());
        } else {
            $index = (int)$position;
        }

        $this->data[$index] = $event->jsonSerialize();
        return true;
    }

    public function addEvent(FileEvent $event)
    {
        $this->data[] = $event->jsonSerialize();
    }

    public function removeEvent(FileEvent $event)
    {
        $filtered = array_filter($this->data, function ($elem) use ($event) {
            return ($elem->path != $event->getPath());
        });

        $this->data = [];

        foreach ($filtered as $ev) {
            $this->data[] = $ev;
        }

    }

    public function dump()
    {
        file_put_contents($this->fileName, json_encode($this->data, JSON_PRETTY_PRINT));
    }

}