<?php namespace Avram\Guard\Services;

use Avram\Guard\Exceptions\GuardFileException;
use Avram\Guard\Site;

class GuardFile
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
            $fileName = GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'guard.json';
        }

        $this->fileName = $fileName;
        $this->file     = new \SplFileInfo($fileName);
        $json           = new \stdClass();

        if ($this->file->isFile() && $this->file->isReadable()) {
            $contents = file_get_contents($this->file->getPathname());
            if (!empty($contents)) {
                $json = json_decode($contents);
            }

            if (!is_object($json)) {
                throw new GuardFileException("File {$fileName} is wrongly formatted! Delete it and use: php guard.phar init");
            }
        }

        $this->initializeData($json);

    }

    /**
     * @param $json
     */
    public function initializeData($json)
    {
        $this->data        = new \stdClass();
        $this->data->sites = (isset($json->sites) && is_array($json->sites)) ? $json->sites : [];

        $this->data->email            = new \stdClass();
        $this->data->email->address   = isset($json->email->address) ? $json->email->address : null;
        $this->data->email->transport = isset($json->email->transport) ? $json->email->transport : 'mail';
        $this->data->email->sendmail  = isset($json->email->sendmail) ? $json->email->sendmail : '/usr/sbin/sendmail -bs';
        $this->data->email->smtp_host = isset($json->email->smtp_host) ? $json->email->smtp_host : '';
        $this->data->email->smtp_port = isset($json->email->smtp_port) ? (int)$json->email->smtp_port : 25;
        $this->data->email->smtp_user = isset($json->email->smtp_user) ? $json->email->smtp_user : '';
        $this->data->email->smtp_pass = isset($json->email->smtp_pass) ? $json->email->smtp_pass : '';
    }

    /**
     * @param null $what
     *
     * @return mixed
     */
    public function getEmail($what = null)
    {
        if (empty($what)) {
            return $this->data->email;
        }

        return $this->data->email->{$what};
    }

    /**
     * @param      $what
     * @param null $value
     */
    public function setEmail($what, $value = null)
    {
        if (is_object($what)) {
            $this->data->email = $what;
        }

        $this->data->email->{$what} = $value;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSites()
    {
        $sites = [];

        foreach ($this->data->sites as $site) {
            $sites[] = Site::fromJSONObject($site);
        }

        return $sites;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        $paths = [];

        foreach ($this->data->sites as $site) {
            $paths[] = $site->path;
        }

        return $paths;
    }

    /**
     * @param Site $site
     */
    public function addSite(Site $site)
    {
        $this->data->sites[] = $site->jsonSerialize();
    }

    /**
     * @param $name
     */
    public function removeSite($name)
    {
        $this->data->sites = array_filter($this->data->sites, function ($elem) use ($name) {
            return ($elem->name != $name);
        });
    }

    /**
     * @param Site $site
     * @param int  $position
     *
     * @return bool
     */
    public function updateSite(Site $site, $position = null)
    {
        if ($position === null) {
            $index = $this->findSiteIndexByName($site->getName());
            if ($index === null) {
                return false;
            }
        } else {
            $index = (int)$position;
        }

        $this->data->sites[$index] = $site->jsonSerialize();
        return true;
    }

    /**
     * @param $name
     *
     * @return Site|null
     * @throws \Exception
     */
    public function findSiteByName($name)
    {
        foreach ($this->data->sites as $site) {
            if ($site->name == $name) {
                return Site::fromJSONObject($site);
            }
        }

        return null;
    }

    /**
     * @param $name
     *
     * @return null|int
     */
    public function findSiteIndexByName($name)
    {
        for ($i = 0; $i < count($this->data->sites); $i++) {
            $site = $this->data->sites[$i];
            if ($site->name == $name) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param $path
     *
     * @return Site|null
     * @throws \Exception
     */
    public function findSiteByPath($path)
    {
        foreach ($this->data->sites as $site) {
            if ($site->path == $path) {
                return Site::fromJSONObject($site);
            }
        }

        return null;
    }


    /**
     * @param $path
     *
     * @return Site|null
     */
    public function findSiteByLongPath($path)
    {
        $sites = $this->getSites();

        /** @var Site $testSite */
        foreach ($sites as $testSite) {
            $testPath = $path;

            while ((strlen($testPath) > 1)) {
                if ($testPath == $testSite->getPath()) {
                    return $testSite;
                }
                $testPath = dirname($testPath);
            }
        }

        return null;
    }


    /**
     * Write guardFile configuration
     */
    public function writeGuardFile()
    {
        file_put_contents($this->file->getPathname(), json_encode($this->data, JSON_PRETTY_PRINT));
    }

    /**
     * @return \SplFileInfo
     */
    public function watchFile()
    {
        return new \SplFileInfo(GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'.watchlist');
    }

    /**
     * Write watch paths to file
     */
    public function writeWatchFile()
    {
        file_put_contents($this->watchFile()->getPathname(), implode(PHP_EOL, $this->getPaths()));
    }

    /**
     * Dump guardFile config file, watched paths, etc...
     */
    public function dump()
    {
        $this->writeGuardFile();
        $this->writeWatchFile();
    }
}