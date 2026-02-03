<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Command\FilenameParseCommand;
use Symfony\Component\Console\Application;

$application = new Application('filename-parser', '1.0.0');
$application->addCommands([
    new FilenameParseCommand(),
]);
$application->setDefaultCommand('parse:filenames', true);

exit($application->run());
