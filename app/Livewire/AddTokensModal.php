<?php

namespace App\Livewire;

use Livewire\Component;

class AddTokensModal extends Component
{
    public $showModal = true;
    public $account_id = null;

    protected $listeners = [
        'showAddModal' => 'show',
        'hideAddModal' => 'hide'
    ];

    public function show($account_id)
    {
        $this->showModal = true;
        $this->account_id = $account_id;
    }

    public function hide()
    {
        $this->showModal = false;
    }


    public function render()
    {
        return view('livewire.add-tokens-modal');
    }
}
