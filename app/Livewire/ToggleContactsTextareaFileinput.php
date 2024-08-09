<?php

namespace App\Livewire;

use Livewire\Component;

class ToggleContactsTextareaFileinput extends Component
{
    public $inputType = 'text';
    public $textareaName = 'content';
    public $fileInputName = 'file';
    public $selectorName = 'selector';
    public $onChange = '';
    public $radioInputChange = '';

    public function render()
    {
        return view('livewire.toggle-contacts-textarea-fileinput');
    }
}
