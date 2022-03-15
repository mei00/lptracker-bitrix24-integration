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

$obLPTracker = new LPTracker(
    [
        'login' => 'heaven.st@yandex.ru',
        'password' => 'wzT852',
        'service' => 'b24integration',
    ]
);
$project = $obLPTracker->getProjectList()[0];

// $obLPTracker->setProjectCallbackUrl($project, 'https://run.jn5.ru/apiHandlerLPTracker.php');

if ($_REQUEST['event'] == 'ONCRMLEADADD') {
    $leadId = intval($_REQUEST['data']['FIELDS']['ID']);

    $arLead = $obRest->send('crm.lead.get', ['ID' => $leadId]);

    $logger->info(print_r('b24 lead!', true));
    $logger->info(print_r($arLead, true));

    if ($arLead && $arLead['STATUS_ID'] != 'UC_DERSGR') {
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
            'custom' => [
                LPT_LEAD_B24_ID => $arLead['ID']
            ],
        ];
        $obLptLead = $obLPTracker->createLead($contact, $leadData, $options);

        if ($obLptLead) {
            /* Обновить лид в Б24 */
            /* Добавить ID соответствующего лида в LPTracker */

            $arUpdateFields = [
                'ID' => $arLead['ID'],
                'FIELDS' => [
                    B24_LEAD_LPTRACKER_ID => $obLptLead->getId(),
                ],
            ];

            $requestResult = $obRest->send('crm.lead.update', $arUpdateFields);

            $logger->info(print_r($requestResult, true));
        }

    }
}


/* Отправка в LPTracker при обновлении существующего лида */

if ($_REQUEST['event'] == 'ONCRMLEADUPDATE') {
    $leadId = intval($_REQUEST['data']['FIELDS']['ID']);

    $arLead = $obRest->send('crm.lead.get', ['ID' => $leadId]);

    if ($arLead['STATUS_ID'] == B24_STAGE_CALL && !$arLead[B24_LEAD_LPTRACKER_ID]) {

        /* Создание лида в LPTracker */
        $arContact = $obRest->send('crm.contact.get', ['ID' => $arLead['CONTACT_ID']]);
        $leadName = $arContact['NAME'].' '.$arContact['LAST_NAME'];
        $leadPhone = current($arContact['PHONE'])['VALUE'];

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
            'custom' => [
                LPT_LEAD_B24_ID => $arLead['ID']
            ],
        ];
        $obLptLead = $obLPTracker->createLead($contact, $leadData, $options);

        if ($obLptLead) {
            /* Перенести лид на стадию "На прозвон" */
            $obLPTracker->changeLeadFunnel($obLptLead->getId(), LPT_STAGE_CALL);

            /* Обновить лид в Б24 */
            /* Добавить ID соответствующего лида в LPTracker */

            $arUpdateFields = [
                'ID' => $arLead['ID'],
                'FIELDS' => [
                    B24_LEAD_LPTRACKER_ID => $obLptLead->getId(),
                ],
            ];

            $requestResult = $obRest->send('crm.lead.update', $arUpdateFields);

            $logger->info(print_r('on call!', true));
            $logger->info(print_r($requestResult, true));
        }
    }


    $logger->info(print_r('ONCRMLEADUPDATE!', true));
    $logger->info(print_r($arLead, true));
}
