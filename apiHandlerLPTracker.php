<?php
require_once 'vendor/autoload.php';
require_once 'classes/Jungle/B24.php';

use LPTracker\LPTracker;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

define('B24_URL', 'b24-n3i5ee.bitrix24.ru');
define('B24_TOKEN', 'u87r6ytc3vdkbcan');


$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/leadsTPTracker.log', Logger::DEBUG));

$logMessage = print_r($_REQUEST, true);
$logger->info($logMessage);
