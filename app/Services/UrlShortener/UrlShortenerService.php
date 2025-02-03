<?php

namespace App\Services\UrlShortener;

use App\Jobs\RegisterShortDomainJob;
use App\Models\CampaignShortUrl;
use App\Models\UrlShortener;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function create(array $domainUrls)
    {
        foreach ($domainUrls as $endpoint) {
            $data = [
                'name' => $endpoint,
                'endpoint' => 'dummy',
                'is_registered' => false,
                'is_propagated' => false,
            ];

            UrlShortener::create($data);
        }

        dispatch(new RegisterShortDomainJob());

        return ['message' => 'URL Shortener put in queue for registration.'];
    }

    public function update($id, Request $request)
    {
        return ['error' => 'URL Shortener update is not allowed.'];

//        $validator = Validator::make($request->all(), [
//            'name' => ['required', 'string', 'max:255'],
//            'endpoint' => ['required', 'string', 'max:2048'],
//        ]);
//
//        if ($validator->fails()) {
//            return ['errors' => $validator->errors()];
//        }
//
//        $data = $validator->validated();
//
//        UrlShortener::whereId($id)->update($data);
//
//        return ['message' => 'URL Shortener updated successfully.'];
    }

    public function delete($id)
    {
        $biddingRecords = CampaignShortUrl::where('url_shortener_id', $id)->first();

        if ($biddingRecords) {
            return ['error' => 'URL Shortener cannot be deleted because it is being used in a campaign.'];
        }

        if (UrlShortener::destroy($id)) {
            return ['message' => 'URL Shortener deleted successfully.'];
        }

        return ['error' => 'URL Shortener not found.'];
    }
}
