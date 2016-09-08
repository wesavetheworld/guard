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

    public function getEvents()
    {
        $result = [];
        foreach ($this->data as $ev) {
            $result[] = new FileEvent($ev->site, $ev->path, $ev->type, $ev->attempts, $ev->status);
        }

        return $result;
    }

    public function setEvents(array $events)
    {
        $this->data = $events;
    }

    public function findFileEventsIndicesBy(array $args)
    {
        $result = [];
        for ($i = 0; $i < count($this->data); $i++) {
            $shouldContinue = true;
            foreach ($args as $param => $value) {
                if ($this->data->{$param} != $value) {
                    $shouldContinue = false;
                }
            }

            if ($shouldContinue) {
                $result[] = $i;
            }
        }

        return $result;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function findFileEventsBy(array $args)
    {
        $result  = [];
        $indices = $this->findFileEventsIndicesBy($args);

        foreach ($indices as $index) {
            $event    = $this->data[$index];
            $result[] = new FileEvent($event->site, $event->path, $event->type, $event->attempts, $event->status);
        }

        return $result;
    }

    public function updateFileEvent(FileEvent $event, $position = null)
    {
        if ($position === null) {
            $index = $this->findFileEventsIndicesBy([
                'name' => $event->getSiteName(),
            ])[0];
            if (count($index) < 1) {
                return false;
            }
            $index = $index[0];
        } else {
            $index = (int)$position;
        }

        $this->data[$index] = $event->jsonSerialize();
        return true;
    }

}