<div>

    <div>
        <input type="radio" name="{{ selectorName }}" wire:model="inputType" value="text"> Textarea
        <input type="radio" name="{{ selectorName }}"  wire:model="inputType" value="file"> File Input
    </div>

    @if ($inputType === 'text')
        <textarea name="{{ $textareaName }}" wire:model="content"></textarea>
    @else
        <input type="file" name="{{ $fileInputName }}"  wire:model="file">
    @endif

</div>
