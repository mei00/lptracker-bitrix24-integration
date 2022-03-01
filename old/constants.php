<?php
define('B24_URL', 'b24-n3i5ee.bitrix24.ru');
define('B24_TOKEN', 'u87r6ytc3vdkbcan');

/* Стадии лида LPTracker */
define('LPT_STAGE_NEW', '1648185');
define('LPT_STAGE_IN_PROGRESS', '1648195');
define('LPT_STAGE_LOSE', '1648184');
define('LPT_STAGE_CALL', '1648186');

/* Дополнительные свойства лида LPTracker */
define('LPT_LEAD_B24_ID', 1803704);

/* Стадии лида Битрикс24 */
define('B24_STAGE_DONT_LOAD', 'UC_DERSGR');
define('B24_STAGE_NEW', 'NEW');
define('B24_STAGE_IN_PROGRESS', 'IN_PROCESS');
define('B24_STAGE_LOSE', 'JUNK');
define('B24_STAGE_CALL', 'UC_AUOZSH');

/* Дополнительные свойства лида Битрикс24> */
define('B24_LEAD_LPTRACKER_ID', 'UF_CRM_1645261237416');

/* Сопоставление стадий лидов LPTracker и Битрикс24 */
define('STAGES_LPT_TO_B24', [
    LPT_STAGE_NEW => B24_STAGE_NEW,
    LPT_STAGE_IN_PROGRESS => B24_STAGE_IN_PROGRESS,
    LPT_STAGE_LOSE => B24_STAGE_LOSE,
    LPT_STAGE_CALL => B24_STAGE_CALL,
]);
