<?php
require "vendor/autoload.php";

use Symfony\Component\Console\Application;

// define commands
$commands = [
    Avram\Guard\Command\SiteAdd::class,
    Avram\Guard\Command\Watch::class,
];

// ensure ~/.guard folder exists
$user = get_current_user();
define('GUARD_SYSTEM_USER', $user);

$appFolder = new SplFileInfo("/home/{$user}/.guard");
if (!$appFolder->isDir()) {
    mkdir($appFolder, 0755, true);
}
define('GUARD_USER_FOLDER', $appFolder);

// bootstrap the application
$application = new Application('Guard', '0.1-dev');
foreach ($commands as $command) {
    $application->add(new $command);
}

// and off we go!
$application->run();