<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignSwaggerController extends SwaggerController
{
    public function markAsIgnoreFromQueue(Request $request) {}

    public function markAsNotIgnoreFromQueue(Request $request) {}

    public function index(Request $request) {}

    public function show(int $id, Request $request) {}

    public function store(CampaignStoreRequest $request) {}

    public function update(CampaignUpdateRequest $request, Campaign $campaign) {}

    public function destroy(Campaign $campaign) {}
}
