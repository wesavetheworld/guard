<?php

require "vendor/autoload.php";

use Avram\Guard\Command\Test;
use Symfony\Component\Console\Application;

$application = new Application('Guard', '0.1-dev');
$application->add(new Avram\Guard\Command\Test());
$application->run();