<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Start extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start watching all sites\' folders for changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $sites = $this->guardFile->getSites();
        if (count($sites) < 1) {
            $this->error("No sites configured to watch! Use: guard site:add");
        }

        $output->writeln("Setting up watches for all configured sites. This may take a while...");
        $output->writeln("To stop watching use: guard stop");

        $watchFile = $this->guardFile->watchFile()->getPathname();

        $process = new Process("inotifywait -m -r -e modify -e create -e move -e delete --format '%w%f %e' --fromfile={$watchFile}");
        $process->setTimeout(0);
        $process->start();

        $pid        = getmypid();
        $processPid = (int)$process->getPid();
        $output->writeln("My PID is {$pid}, watcher PID is {$processPid}");

        $assumeNextPid = $processPid + 1;
        $pids          = [$pid, $processPid, $assumeNextPid];

        $pidFile = GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'.pidfile';
        file_put_contents($pidFile, implode(' ', $pids));

        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                if (stripos($buffer, "Setting up watches") !== 0) {
                    $output->writeln($buffer);
                }
            } else {
                $buffer = str_replace(PHP_EOL, ' ', $buffer);
                $parts  = explode(' ', $buffer);
                $path   = trim($parts[0]);
                $event  = trim($parts[1]);
                $this->handle($path, $event);
            }
        });

        unlink($pidFile);

        echo $process->getOutput();
    }

    protected function handle($filePath, $event)
    {
        $dir  = dirname($filePath);
        $site = $this->guardFile->findSiteByLongPath($filePath);

        if ($site == null) {
            $this->log("Site not found on path {$dir}");
            return;
        }

        //check if file type is not guarded
        if (!fnmmatch($site->getTypes(), $filePath)) {
            return;
        }

        //check if file is always allowed to be modified
        $ignored      = $site->getExcludes();
        $shouldIgnore = false;
        foreach ($ignored as $ignore) {
            if (strpos($filePath, $ignore) === 0) {
                $shouldIgnore = true;
                $this->log("{$filePath} is on the excluded path so action {$event} will be allowed!");
            }
        }

        if ($shouldIgnore) {
            return;
        }

        $relativeFilePath   = ltrim(str_replace($site->getPath(), '', $filePath), DIRECTORY_SEPARATOR);
        $quarantineFilePath = $site->quarantinePath($relativeFilePath);
        $quarantinePath     = dirname($quarantineFilePath);
        $backupFilePath     = $site->backupPath($relativeFilePath);

//        $this->log("Processing {$event}...");

        switch ($event) {
            case 'MOVED_TO':
            case 'CREATE':
                //file created, quarantine and notify
                if (!is_dir($quarantinePath)) {
                    mkdir($quarantinePath, 0755, true);
                }
                if (!is_file($filePath)) {
                    return;
                }

                $newFileHash = md5_file($filePath);
                $oldFileHash = is_file($backupFilePath) ? md5_file($backupFilePath) : false;
                if ($newFileHash === $oldFileHash) {
                    $this->log("{$backupFilePath} is the same as {$filePath}");
                    return;
                }

                rename($filePath, $quarantineFilePath);
                $this->log("{$filePath} quarantined to {$quarantineFilePath}");

//                Event::block($filePath, $event, $site);
            //@TODO: log blocked event so it can be allowed

                break;

            case 'MODIFY':
                //file exists, first quarantine
                if (!is_dir($quarantinePath)) {
                    mkdir($quarantinePath, 0755, true);
                }
                if (!is_file($filePath)) {
                    return;
                }

                $newFileHash = md5_file($filePath);
                $oldFileHash = is_file($backupFilePath) ? md5_file($backupFilePath) : false;
                if ($newFileHash === $oldFileHash) {
                    $this->log("{$backupFilePath} is the same as {$filePath}");
                    return;
                }

                copy($filePath, $quarantineFilePath);
                $this->log("{$filePath} quarantined to {$quarantineFilePath}");

//                Event::block($filePath, $event, $site);
                //@TODO: log blocked event so it can be allowed


                //...then restore backup if exists or remove file if not
                if (is_file($backupFilePath)) {
                    if ($newFileHash !== $oldFileHash) {
                        copy($backupFilePath, $filePath);
                        $this->log("{$backupFilePath} restored to {$filePath}");
                    } else {
                        $this->log("{$backupFilePath} is the same as {$filePath}");
                    }
                } else {
                    unlink($filePath);
                    $this->log("{$filePath} is removed because no backup file is found!");
                }
                break;

            case 'MOVED_FROM':
            case 'DELETE':
                //file removed, restore
                if (is_file($backupFilePath)) {
                    copy($backupFilePath, $filePath);
                    $this->log("Restored {$backupFilePath} to {$filePath}");

//                    Event::block($filePath, $event, $site);
                    //@TODO: log blocked event so it can be allowed

                } else {
                    $this->log("No backup exists at {$backupFilePath}");
                }
                break;

            default:
                $this->log("No action defined for {$event}");
        }
    }

    public function log($line)
    {
//        $this->outputInterface->writeln($line);
        file_put_contents(GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'guard.log', $line.PHP_EOL, FILE_APPEND);
    }

}