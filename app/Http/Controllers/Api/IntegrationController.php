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

    public function seedLeads()
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


    public function exportLeads()
    {
        $obRest = new B24([]);

        $b24Request = [
            'filter' => [
                'STATUS_ID' => B24_STAGE_CALL,
                B24_QUEUED => false,
            ],
        ];

        $arLeads = $obRest->send('crm.lead.list', $b24Request);

        foreach ($arLeads as $arLead) {
            $arContact = $obRest->send('crm.contact.get', ['ID' => $arLead['CONTACT_ID']]);
            $leadName = $arContact['NAME'].' '.$arContact['LAST_NAME'];
            $leadPhone = current($arContact['PHONE'])['VALUE'];

            $lead = LeadsQueue::where('bitrix_id', $arLead['ID'])->first();

            if ($lead === null) {
                LeadsQueue::create([
                   'lptracker_id' => 0,
                   'bitrix_id' => $arLead['ID'],
                   'name' => $leadName,
                   'phone' => $leadPhone,
                   'is_exported' => 0,
                ]);

                $arUpdateFields = [
                    'ID' => $arLead['ID'],
                    'FIELDS' => [
                        B24_QUEUED => true,
                    ],
                ];
                $obRest->send('crm.lead.update', $arUpdateFields);
            }
        }

        return true;
    }


    public function importLeads()
    {
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
                    'funnel' => "1648186",
                ];
                $options = [
                    'callback' => false
                ];
                $obLptLead = $obLPTracker->createLead($contact, $leadData, $options);

                if ($obLptLead) {
                    $lead->lptracker_id = $obLptLead->getId();
                    $lead->is_exported = 1;

                    $lead->save();
                }
            }

            return true;
        } else {

            return 'All data has been exported already';
        }
    }


}
