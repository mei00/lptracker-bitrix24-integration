<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// use App\Models\LeadsQueue;

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
        // $games = Room::where('creator', Auth::id())->with('language')->get();

        return ['1', 2, 3];
    }


}
