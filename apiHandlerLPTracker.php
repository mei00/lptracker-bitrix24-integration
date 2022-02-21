<?php
require_once 'vendor/autoload.php';
require_once 'classes/Jungle/B24.php';
require_once 'constants.php';

use LPTracker\LPTracker;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/leadsTPTracker.log', Logger::DEBUG));

$obRest = new \Jungle\B24([]);

$arLptLead = json_decode($_REQUEST['data'], true);

$logger->info(print_r('lpt lead!', true));
$logger->info(print_r($arLptLead, true));

if ($arLptLead['action'] == 'update') {

    /* Получение ассоциативного массива дополнительных свойств */
    $arCustomFields = [];
    foreach ($arLptLead['custom'] as $arCustomField) {
        $arCustomFields[$arCustomField['id']] = [
            'NAME' => $arCustomField['name'],
            'VALUE' => $arCustomField['value'],
        ];
    }

    $b24LeadId = $arCustomFields[LPT_LEAD_B24_ID]['VALUE'];
    $lptLeadStage = $arLptLead['stage']['id'];

    if ($b24LeadId) {
        $arB24Lead = $obRest->send('crm.lead.get', ['ID' => $b24LeadId]);

        $logger->info(print_r(['b24 lead id!'], true));
        $logger->info(print_r($arB24Lead, true));

        if ($arB24Lead['STATUS_ID'] != STAGES_LPT_TO_B24[$lptLeadStage] && $lptLeadStage != LPT_STAGE_CALL) {
            $arUpdateFields = [
                'ID' => $b24LeadId,
                'FIELDS' => [
                    'STATUS_ID' => STAGES_LPT_TO_B24[$lptLeadStage],
                ],
            ];

            $requestResult = $obRest->send('crm.lead.update', $arUpdateFields);
        }
    }

}
