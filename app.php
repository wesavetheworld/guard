<?php

require "vendor/autoload.php";

use Symfony\Component\Console\Application;

$commands = [
    Avram\Guard\Command\Start::class,
];

$application = new Application('Guard', '0.1-dev');

foreach ($commands as $command) {
    $application->add(new $command);
}

$application->run();