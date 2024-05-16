<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampaignController extends Controller
{
        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            $clients = auth()->user()->campaigns()->latest()->paginate(5);
    
            return view('campaigns.index', compact('campaigns'));
        }
    
        /**
         * Show the form for creating a new resource.
         */
        public function create()
        {
            $clients = auth()->user()->clients()->all();
            return view('campaigns.create', compact('clients'));
        }
    
        /**
         * Store a newly created resource in storage.
         */
        public function store(Request $request)
        {
            $request->validate([
                'title' => 'required|string|max:255',
                'client_id' => 'required',
            ]);
    
            $campaign = auth()->user()->campaigns()->create([
                'title' => $request->title,
                'description' => $request->description,
                'client_id' => $request->client_id,
            ]);
    
            return redirect()->route('campaigns.show', $campaign)->with('success', 'Client created successfully.');
        }
    
        /**
         * Display the specified resource.
         */
        public function show(Campaign $campaign)
        {
            return view('campaigns.show', compact('campaign'));
        }

        public function createBroadcastBatch(Campaign $campaign)
        {
            $recipient_lists = auth()->user()->recipients_lists()->all();
            return view('campaigns.broadcast-batch.create', compact('campaign', 'recipient_lists'));
        }


        /**
         * Show the form for editing the specified resource.
         */
        public function edit(Campaign $campaign)
        {
            return view('campaigns.edit', compact('campaign'));
        }
    
        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, Campaign $campaign)
        {
            $request->validate([
                'title' => 'required|string|max:255',
                'client_id' => 'required',
            ]);
    
            $campaign->update([
                'title' => $request->title,
                'description' => $request->description,
                'client_id' => $request->client_id,
            ]);
    
            return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign updated successfully.');
        }
    
        /**
         * Remove the specified resource from storage.
         */
        public function destroy(Campaign $campaign)
        {
            $client->delete();
    
            return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
        }
    }
    