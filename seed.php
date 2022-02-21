<?php
require_once 'vendor/autoload.php';
require_once 'classes/Jungle/B24.php';
require_once 'constants.php';

use LPTracker\LPTracker;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/leads.log', Logger::DEBUG));

$obRest = new \Jungle\B24([]);

for ($i = 1; $i <= 1000; $i++) {

    echo $i;
    echo '<br>';

    $name = 'Петр';
    $lastName = 'Петров';

    $arContactFields = [
        'FIELDS' => [
            'NAME' => $name,
            'LAST_NAME' => $lastName,
            'TYPE_ID' => 'CLIENT',
            'PHONE' => [
                [
                    'VALUE' => '+79061233411',
                    'VALUE_TYPE' => 'PERSONAL'
                ]
            ]
        ],
        'PARAMS' => ['REGISTER_SONET_EVENT' => 'N'],
    ];

    $contactId = $obRest->send('crm.contact.add', $arContactFields);

    $arLeadFields = [
        'FIELDS' => [
            'CONTACT_ID' => $contactId,
            'STATUS_ID' => B24_STAGE_DONT_LOAD,
        ],
        'PARAMS' => ['REGISTER_SONET_EVENT' => 'N'],
    ];

    $requestResult = $obRest->send('crm.lead.add', $arLeadFields);

    break;
}
