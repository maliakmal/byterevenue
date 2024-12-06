<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Requests\JobRegenerateRequest;
use Illuminate\Http\Request;

class JobsSwaggerController extends SwaggerController
{
    public function postIndex(Request $request) {}

    public function regenerateUnsent(JobRegenerateRequest $request) {}

    public function updateSentMessage(Request $request) {}

    public function updateClickMessage(Request $request) {}
}
