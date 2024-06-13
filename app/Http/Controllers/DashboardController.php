<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataFeed;
use App\Models\Campaign;
use App\Models\User;
use App\Models\BroadcastLog;

class DashboardController extends Controller
{
    public function index()
    {
        $dataFeed = new DataFeed();
        $accounts = [];
        $params = [];
        if(auth()->user()->hasRole('admin')):
            $campaigns = Campaign::select()->orderby('id', 'desc')->get()->take(100);
            $accounts = User::select()->orderby('id', 'desc')->get()->take(10);
            $params['total_in_queue'] = BroadcastLog::select()->count();
            $params['total_not_downloaded_in_queue'] = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
               
        else:
            $campaigns = auth()->user()->campaigns()->latest()->get()->take(30);
        endif;

        return view('dashboard', compact('dataFeed', 'campaigns', 'accounts', 'params'));
    }

    /**
     * Displays the analytics screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function analytics()
    {
        return view('pages/dashboard/analytics');
    }

    /**
     * Displays the fintech screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function fintech()
    {
        return view('pages/dashboard/fintech');
    }
}
