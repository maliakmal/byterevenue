<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckAdminRole;
use App\Models\UrlShortener;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use App\Services\UrlShortener\UrlShortenerService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

class UrlShortenerController extends Controller
{
    /**
     * @param UrlShortenerService $urlShortenerService
     */
    public function __construct(UrlShortenerService $urlShortenerService)
    {
        $this->middleware(['auth', CheckAdminRole::class]);
        $this->urlShortenerService = $urlShortenerService;
    }

    public function index()
    {
        $urlShorteners = UrlShortener::query();
        $filter = [
            'is_propagated'=> request('is_propagated'),
            'sortby'=> request('sortby', 'id_desc'),
        ];

        if (!is_null($filter['is_propagated'])){
            $urlShorteners->where('is_propagated', $filter['is_propagated']);
        }

        if (!empty($filter['sortby'])) {
            switch($filter['sortby']) {
                case 'id_desc':
                    $urlShorteners->orderby('id', 'desc');
                    break;
                case 'id_asc':
                    $urlShorteners->orderby('id');
                    break;
                case 'url_asc':
                    $urlShorteners->orderby('name');
                    break;
                case 'url_desc':
                    $urlShorteners->orderby('name', 'desc');
                    break;
            }
        }

        $urlShorteners = $urlShorteners->paginate(10);

        return view('url_shorteners.index', compact('filter' ,'urlShorteners'));
    }

    public function create()
    {
        return view('url_shorteners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'endpoints' => 'required|array',
            'endpoints.*' => 'required|string|max:180|unique:url_shorteners,name',
        ]);

        // create new short domain records
        $response = $this->urlShortenerService->create($request->endpoints);

        if (isset($response['error'])) {
            return redirect()->route('url_shorteners.index')->with('success', $response['error']);
        }

        return redirect()->route('url_shorteners.index')->with('success', $response['message']);
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
