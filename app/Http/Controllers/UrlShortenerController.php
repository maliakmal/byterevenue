<?php

namespace App\Http\Controllers;
use App\Models\UrlShortener;

use Illuminate\Http\Request;

class UrlShortenerController extends Controller
{
    public function index()
    {
        $urlShorteners = UrlShortener::select()->paginate(5);
        return view('url_shorteners.index', compact('urlShorteners'));
    }

    public function create()
    {
        return view('url_shorteners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'endpoint' => 'required|string|max:255',
        ]);

        UrlShortener::create($request->all());
        return redirect()->route('url_shorteners.index')->with('success', 'URL Shortener created successfully.');
    }

    public function edit(UrlShortener $urlShortener)
    {
        return view('url_shorteners.edit', compact('urlShortener'));
    }

    public function update(Request $request, UrlShortener $urlShortener)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'endpoint' => 'required|string|max:255',
        ]);

        $urlShortener->update($request->all());
        return redirect()->route('url_shorteners.index')->with('success', 'URL Shortener updated successfully.');
    }

    public function destroy(UrlShortener $urlShortener)
    {
        $urlShortener->delete();
        return redirect()->route('url_shorteners.index')->with('success', 'URL Shortener deleted successfully.');
    }
}
