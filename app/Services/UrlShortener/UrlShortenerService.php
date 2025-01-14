<?php

namespace App\Services\UrlShortener;

use App\Models\UrlShortener;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

class UrlShortenerService
{
    public function getAll(Request $request)
    {
        $query     = UrlShortener::withCount(['campaignShortUrls']);
        $sortBy    = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($request->filled('is_propagated')) {
            $query = $query->propagatedFilter($request->is_propagated);
        }

        if ($request->filled('url')) {
            $query = $query->whereUrl($request->url);
        }

        $urlShorteners = $query->orderBy($sortBy, $sortOrder)->paginate($request->input('per_page', 15));

        return $urlShorteners;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:url_shorteners,name',
            'endpoint' => 'required|string|max:255',
        ]);

        $data = $request->all();
        $request = new RegisterShortDomainRequest(
            name: $data['name'],
            ssl_redirect: true,
            is_ssl: true,
            cloudflare_proxy: true,
            allow_indexing: false
        );

        try {
            $rawResponse = KeitaroCaller::call($request);

            if (isset($rawResponse['error'])) {
                return ['error' => $rawResponse['error']];
            }

            $response = $rawResponse[0];
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
