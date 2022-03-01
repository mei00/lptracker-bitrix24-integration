<?php
require_once 'vendor/autoload.php';
require_once 'classes/Jungle/B24.php';

use LPTracker\LPTracker;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define('B24_URL', 'b24-n3i5ee.bitrix24.ru');
define('B24_TOKEN', 'u87r6ytc3vdkbcan');

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/setCallback.log', Logger::DEBUG));

$obLPTracker = new LPTracker(
    [
        'login' => 'heaven.st@yandex.ru',
        'password' => 'wzT852',
        'service' => 'b24integration',
    ]
);
$project = $obLPTracker->getProjectList()[0];
$result = $obLPTracker->setProjectCallbackUrl($project, 'https://run.jn5.ru/apiHandlerLPTracker.php');

$logMessage = print_r($project, true);
$logger->info($logMessage);
