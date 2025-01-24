<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CampaignShortUrl;

class AliasesController extends Controller
{
    public function index()
    {
        $aliases = CampaignShortUrl::latest()->get();

        return view('aliases.index', compact('aliases'));
    }

    public function refresh($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            return back()->with('error', 'You do not have permission to perform this action');
        }

        $aliases = CampaignShortUrl::find($id);

        if (!$aliases) {
            return back()->with('error', 'Alias not found');
        }

        $aliases->update([
            'error' => null,
        ]);

        return back()->with('success', 'Alias refreshed successfully');
    }
}
