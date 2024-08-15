<?php

namespace App\Http\Controllers;
use App\Models\UrlShortener;

use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\CreateShortDomainRequest;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

class UrlShortenerController extends Controller
{
    public function index()
    {

        $urlShorteners = UrlShortener::query();
        $filter = array(
            'is_propagated'=> request('is_propagated')!=''?request('is_propagated'):null,
            'sortby'=> request('sortby')?request('sortby'):'id_desc',
        );

        if(!is_null($filter['is_propagated'])){
            $urlShorteners->where('is_propagated', $filter['is_propagated']);
        }


        if(!empty($filter['sortby'])){
            switch($filter['sortby']){
                case 'id_desc':
                    $urlShorteners->orderby('id', 'desc');
                    break;
                case 'id_asc':
                    $urlShorteners->orderby('id', 'asc');
                    break;
                case 'url_asc':
                    $urlShorteners->orderby('name', 'asc');
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
            'name' => 'required|string|max:255|unique:url_shorteners,name',
            'endpoint' => 'required|string|max:2048',
        ]);
        $inputs = $request->all();
        $request = new RegisterShortDomainRequest($inputs['name'],null, null, null,
            null, true, true, true, false);
        $caller = new KeitaroCaller();
        $response = null;
        try{
            $response = $caller->call($request)[0];
            $inputs['is_registered'] = true;
            $inputs['is_propagated'] = false;

            $inputs['asset_id'] = $response['id'];
            $inputs['response'] = json_encode($response);
        }
        catch (RequestException $exception){
            return redirect()->route('url_shorteners.index')->with('error', $exception->getMessage());
        }
        catch (\Exception $exception){
            report($exception);
            return redirect()->route('url_shorteners.index')->with('error', 'Error Sync URL Shortener');
        }
        UrlShortener::create($inputs);
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
