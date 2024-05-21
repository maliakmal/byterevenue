<?php

namespace App\Livewire;

use Livewire\Component;

class MessageCrud extends Component
{
    public $message;
    public $modalFormVisible = false;
    public $modalConfirmDeleteVisible = false;
    public $modalConfirmationText;
    public $campaignID = 0;
    public $userID = 0;

    public function render()
    {
        return view('livewire.message-crud', [
            'messages' => Campaign::find($this->campaignID)->messages()->all(),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->modalFormVisible = true;
    }

    public function closeModal()
    {
        $this->modalFormVisible = false;
    }

    public function resetInputFields()
    {
        $this->message = null;
    }

    public function store()
    {
        $this->validate([
            'message.subject' => 'required',
            'message.body' => 'required',
            'message.target_url' => 'nullable|url',
        ]);

        $this->message->user_id = auth()->user()->id;
        $this->message->campaign_id = $campaignID;

        Message::create($this->message);

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit(Message $message)
    {
        $this->message = $message;
        $this->openModal();
    }

    public function update()
    {
        $this->validate([
            'message.text' => 'required',
            'message.url' => 'nullable|url',
        ]);
        $this->message->user_id = auth()->user()->id;
        $this->message->campaign_id = $campaignID;

        $this->message->save();

        $this->closeModal();
        $this->resetInputFields();
    }

    public function deleteConfirmation($id)
    {
        $this->modalConfirmDeleteVisible = true;
        $this->modalConfirmationText = "Are you sure you want to delete this message?";
        $this->messageIdBeingDeleted = $id;
    }

    public function deleteMessage()
    {
        Message::destroy($this->messageIdBeingDeleted);
        $this->modalConfirmDeleteVisible = false;
    }
}