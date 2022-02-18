<?php
require_once 'vendor/autoload.php';
require_once 'classes/Jungle/B24.php';

use LPTracker\LPTracker;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define('B24_URL', 'b24-n3i5ee.bitrix24.ru');
define('B24_TOKEN', 'u87r6ytc3vdkbcan');

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/leads.log', Logger::DEBUG));

$obLPTracker = new LPTracker(
    [
        'login' => 'heaven.st@yandex.ru',
        'password' => 'wzT852',
        'service' => 'b24integration',
    ]
);
$project = $obLPTracker->getProjectList()[0];

$obLPTracker->setProjectCallbackUrl($project, 'https://run.jn5.ru/apiHandlerLPTracker.php');

if ($_REQUEST['event'] == 'ONCRMLEADADD') {
    $obRest = new \Jungle\B24([]);

    $leadId = intval($_REQUEST['data']['FIELDS']['ID']);

    $arLead = $obRest->send('crm.lead.get', ['ID' => $leadId]);

    if ($arLead) {
        if ($arLead['CONTACT_ID']) {
            $arContact = $obRest->send('crm.contact.get', ['ID' => $arLead['CONTACT_ID']]);
            $leadName = $arContact['NAME'].' '.$arContact['LAST_NAME'];
            $leadPhone = current($arContact['PHONE'])['VALUE'];
        }
        if ($arLead['COMPANY_ID']) {
            /* Для демонстрации загружаем только контакты, не компании */
        }

        /* Создание лида в LPTracker */

        $details = [
            [
                'type' => 'phone',
                'data' => $leadPhone,
            ],
        ];

        $contactData = [
            'name' => $leadName,
        ];

        $contact = $obLPTracker->createContact($project->getId(), $details, $contactData, []);

        $leadData = [
            'name' => $leadName,
            'source' => 'Битрикс24',
        ];

        $options = [
            'callback' => false,
        ];

        $lead = $obLPTracker->createLead($contact, $leadData, $options);
    }

    $logMessage = print_r($arLead, true);
    $logger->info($logMessage);
}
