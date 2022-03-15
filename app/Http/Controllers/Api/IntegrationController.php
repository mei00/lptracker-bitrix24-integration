<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeadsQueue;

use LPTracker\LPTracker;

class IntegrationController extends Controller
{
    /**
     * Handle request from Bitrix24
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function handle(Request $request)
    {
        define('B24_URL', 'b24-4f8lrq.bitrix24.ru');
        define('B24_TOKEN', '167gcio6199wev6l');

        define('LPT_LEAD_B24_ID', 1803704);

        /*
         * Создание записи в таблице очереди лидов

        $lead = LeadsQueue::create([
            'lptracker_id' => 5,
            'bitrix_id' => 6,
            'name' => 'Петр Иванов',
            'phone' => '+7 (982) 324-32-12',
            'is_exported' => 0,
        ]);
        */


        $obLPTracker = new LPTracker(
            [
                'login' => 'heaven.st@yandex.ru',
                'password' => 'wzT852',
                'service' => 'b24integration',
            ]
        );
        $project = $obLPTracker->getProjectList()[0];

        /* Создание лида в LPTracker */
        $details = [
            [
                'type' => 'phone',
                'data' => '+7 (982) 324-32-12',
            ],
        ];
        $contactData = [
            'name' => 'Петр Иванов',
        ];
        $contact = $obLPTracker->createContact($project->getId(), $details, $contactData, []);
        $leadData = [
            'name' => 'Петр Иванов',
            'source' => 'Битрикс24',
        ];
        $options = [
            'callback' => false,
            'custom' => [
                LPT_LEAD_B24_ID => 444,
            ],
        ];
        $obLptLead = $obLPTracker->createLead($contact, $leadData, $options);


        return $obLptLead;

        return [1, 2, 3];
    }


    public function seedLeads(Request $request)
    {
        define('B24_URL', 'b24-4f8lrq.bitrix24.ru');
        define('B24_TOKEN', '167gcio6199wev6l');

        define('LPT_LEAD_B24_ID', 1803704);

        /*
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
        */

    }


    public function importLeads(Request $request)
    {

        return "I'M HERE";
    }

    public function exportLeads(Request $request)
    {
        /*
         1. Получить 20 ещё не экспортированных записей LeadsQueue
         2. В цикле по ним создать N лидов в LPTracker
         3. В каждой итерации цикла - обновлять соответствующую запись LeadsQueue
         */

        $leads = LeadsQueue::where('is_exported', false)->get();

        $arLeads = [];
        foreach ($leads as $lead) {
            $arLeads[] = $lead->toArray();
        }

        return print_r($arLeads, true);
    }


}
