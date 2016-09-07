<?php
require "vendor/autoload.php";

use Symfony\Component\Console\Application;

// define commands
$commands = [
    Avram\Guard\Commands\SiteAdd::class,
    Avram\Guard\Commands\SiteSet::class,
    Avram\Guard\Commands\SiteList::class,
    Avram\Guard\Commands\SiteRemove::class,
    Avram\Guard\Commands\SiteBackup::class,

    Avram\Guard\Commands\EmailSet::class,
    Avram\Guard\Commands\EmailShow::class,

    Avram\Guard\Commands\Start::class,
    Avram\Guard\Commands\Stop::class,
];

// ensure ~/.guard folder exists
$user = get_current_user();
define('GUARD_SYSTEM_USER', $user);

$appFolder = new SplFileInfo("/home/{$user}/.guard");
if (!$appFolder->isDir()) {
    mkdir($appFolder->getPathname(), 0755, true);
}
define('GUARD_USER_FOLDER', $appFolder->getPathname());

// bootstrap the application
$application = new Application('Guard', '0.1-dev');
foreach ($commands as $command) {
    $application->add(new $command);
}

// and off we go!
$application->run();