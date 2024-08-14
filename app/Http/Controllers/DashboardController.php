<?php

namespace App\Http\Controllers;

use App\Models\RecipientsList;
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
        $has_campaign = Campaign::where('user_id', auth()->user()->id)->exists();
        $has_reception_list = RecipientsList::where('user_id', auth()->user()->id)->exists();
        return view('dashboard', compact('dataFeed', 'campaigns', 'accounts', 'params', 'has_campaign', 'has_reception_list'));
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

    public function disableIntroductory()
    {
        User::where('id', auth()->id())->update(['show_introductory_screen' => false]);
        return response()->redirectTo('/dashboard');
    }
}
