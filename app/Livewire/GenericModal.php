<?php

namespace App\Livewire;

use Livewire\Component;

class GenericModal extends Component
{
    public $showModal = false;

    protected $listeners = [
        'showModal' => 'show',
        'hideModal' => 'hide'
    ];

    public function show()
    {
        $this->showModal = true;
    }

    public function hide()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.generic-modal');
    }
}
