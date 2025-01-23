<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UpdateSentMessage;

class UpdateSentMessagesController extends Controller
{
    public function index()
    {
        $updateFiles = UpdateSentMessage::latest()->get();

        return view('update-sent-messages', compact('updateFiles'));
    }

    public function download()
    {
        $file = UpdateSentMessage::find(request('id'));

        return response()->download(storage_path('app/'. UpdateSentMessage::FOLDER_NAME . '/' . $file->file_name));
    }
}
