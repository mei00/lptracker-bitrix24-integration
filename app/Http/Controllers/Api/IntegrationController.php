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

        //$leads = Room::where('creator', Auth::id())->with('language')->get();

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


}
