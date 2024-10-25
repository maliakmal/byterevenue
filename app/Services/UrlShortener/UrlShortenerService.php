<?php

namespace App\Services\UrlShortener;

use App\Models\UrlShortener;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\GetDomainRequest;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Validator;

class UrlShortenerService
{
    /**
     * @return array
     */
    public function getAll(Request $request)
    {
        $query = UrlShortener::query();

        if ($request->filled('propagated')) {
            $query = $query->propagatedFilter($request->propagated);
        }

        if ($request->filled('id')) {
            $query = $query->idSort($request->id);
        }

        if ($request->filled('url')) {
            $query = $query->urlSort($request->url);
        }

        $urlShorteners = $query->paginate(15);

        return $urlShorteners;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:url_shorteners,name'],
            'endpoint' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        $data = $validator->validated();
        $request = new RegisterShortDomainRequest(
            name: $data['name'],
            ssl_redirect: true,
            is_ssl: true,
            cloudflare_proxy: true,
            allow_indexing: false
        );

        try {
            $response = KeitaroCaller::call($request)[0];
            $data['is_registered'] = true;
            $data['is_propagated'] = false;

            $data['asset_id'] = $response['id'];
            $data['response'] = json_encode($response);
        } catch (RequestException $exception) {
            return ['error' => $exception->getMessage()];
        } catch (\Exception $exception) {
            report($exception);
            return ['error' => 'Error Sync URL Shortener'];
        }
        UrlShortener::create($data);
        return ['message' => 'URL Shortener created successfully.'];
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        $data = $validator->validated();
        UrlShortener::whereId($id)->update($data);

        return ['message' => 'URL Shortener updated successfully.'];
    }

    public function delete($id)
    {
        UrlShortener::whereId($id)->delete();
        return ['message' => 'URL Shortener deleted successfully.'];
    }
}
