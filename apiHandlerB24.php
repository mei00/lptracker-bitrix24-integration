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
$logger->pushHandler(new StreamHandler(__DIR__.'/leads.log', Logger::DEBUG));

if ($_REQUEST['event'] == 'ONCRMLEADADD') {
    $obRest = new \Jungle\B24([]);

    $leadId = intval($_REQUEST['data']['FIELDS']['ID']);

    $arLead = $obRest->send('crm.lead.get', ['ID' => $leadId]);

    if ($arLead) {
        if ($arLead['CONTACT_ID']) {
            $arContact = $obRest->send('crm.contact.get', ['ID' => $arLead['CONTACT_ID']]);
            $leadName = $arContact['NAME'] . ' ' . $arContact['LAST_NAME'];
            $leadPhone = current($arContact['PHONE'])['VALUE'];
        }
        if ($arLead['COMPANY_ID']) {

        }

        $obLPTracker = new LPTracker(
            [
                'login' => 'heaven.st@yandex.ru',
                'password' => 'wzT852',
                'service' => 'b24integration',
            ]
        );

        $projects = $obLPTracker->getProjectList();

        $details = [
            [
                'type' => 'email',
                'data' => 'contact@example.com'
            ]
        ];

        $contactData = [
            'name'       => $leadName,
            'profession' => 'повар',
            'site'       => 'somecontactsite.ru'
        ];

        $fields = [
            12345 => 'someValue'
        ];

        $contact = $obLPTracker->createContact($projects[0]->getId(), $details, $contactData, $fields);

        $leadData = [
            'name' => $leadName,
            'source' => 'Sdk'
        ];

        $options = [
            'callback' => false
        ];

        $lead = $obLPTracker->createLead($contact, $leadData, $options);

    }

    $logMessage = print_r($arLead, true);

    $logger->info($logMessage);

}
