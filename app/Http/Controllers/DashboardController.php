<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataFeed;
use App\Models\Campaign;

class DashboardController extends Controller
{
    public function index()
    {
        $dataFeed = new DataFeed();
        if(auth()->user()->hasRole('admin')):
            $campaigns = Campaign::select()->orderby('id', 'desc')->get()->take(100);
        else:
            $campaigns = auth()->user()->campaigns()->latest()->get()->take(30);
        endif;

        return view('dashboard', compact('dataFeed', 'campaigns'));
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
