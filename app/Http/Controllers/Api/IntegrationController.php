<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

use App\Models\LeadsQueue;

use App\Helpers\B24;

use LPTracker\LPTracker;

class IntegrationController extends Controller
{
    public function __construct() {
        define('B24_URL', 'b24-4f8lrq.bitrix24.ru');
        define('B24_TOKEN', '167gcio6199wev6l');
        define('B24_STAGE_CALL', 'UC_4SR390');
        define('LPT_LEAD_B24_ID', 1803704);
        define('B24_QUEUED', 'UF_CRM_1647399883514');
    }


    /**
     * Handle request from Bitrix24
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function handle(Request $request)
    {
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
        $names = [];
        for ($i = 1; $i <= 10; $i++) {
            $response = Http::get('https://namey.muffinlabs.com/name.json?count=10&with_surname=true&frequency=all');
            $names = array_merge($names, $response->json());
        }

        $obRest = new B24([]);

        for ($i = 1; $i <= 100; $i++) {

            $name = explode(' ', $names[$i - 1]);

            $arContactFields = [
                'FIELDS' => [
                    'NAME' => $name[0],
                    'LAST_NAME' => $name[1],
                    'TYPE_ID' => 'CLIENT',
                    'PHONE' => [
                        [
                            'VALUE' => '+7' . rand(1000000000, 9999999999),
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
                    'STATUS_ID' => B24_STAGE_CALL,
                ],
                'PARAMS' => ['REGISTER_SONET_EVENT' => 'N'],
            ];

            $requestResult = $obRest->send('crm.lead.add', $arLeadFields);
        }

    }


    public function importLeads(Request $request)
    {
        $obRest = new B24([]);

        $b24Request = [
            'filter' => [
                'STATUS_ID' => B24_STAGE_CALL
            ],
        ];

        $requestResult = $obRest->send('crm.lead.list', $b24Request);

        dump($requestResult);

        return $requestResult;
    }


    public function exportLeads(Request $request)
    {
        /*
         1. Получить 20 ещё не экспортированных записей LeadsQueue
         2. В цикле по ним создать N лидов в LPTracker
         3. В каждой итерации цикла - обновлять соответствующую запись LeadsQueue
         */

        $obLPTracker = new LPTracker(
            [
                'login' => 'heaven.st@yandex.ru',
                'password' => 'wzT852',
                'service' => 'b24integration',
            ]
        );

        $project = $obLPTracker->getProjectList()[0];

        $leads = LeadsQueue::where('is_exported', false)->limit(10)->get();

        if (!$leads->isEmpty()) {
            foreach ($leads as $lead) {

                $details = [
                    [
                        'type' => 'phone',
                        'data' => $lead->phone,
                    ],
                ];
                $contactData = [
                    'name' => $lead->name,
                ];
                $contact = $obLPTracker->createContact($project->getId(), $details, $contactData, []);

                $leadData = [
                    'name' => $lead->name,
                    'source' => 'Битрикс24',
                ];
                $options = [
                    'callback' => false
                ];
                $obLptLead = $obLPTracker->createLead($contact, $leadData, $options);


                $lead->is_exported = 1;

                $lead->save();

            }
            return $obLptLead;
        } else {
            return 'All data has been exported already';
        }
    }


}
