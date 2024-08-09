<div>
    <div class="mb-4">
        <input type="radio" onchange="{{$radioInputChange}}" name="{{ $selectorName }}" class="text-gray-700 " wire:model="inputType" value="text"> Enter Numbers comma seperated
    </div>
    <div class="mb-4">
        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="{{ $textareaName }}" wire:model="content"></textarea>
    </div>
    <div class="mb-4">
        <input type="radio" onchange="{{$radioInputChange}}" name="{{ $selectorName }}" class="text-gray-700 " wire:model="inputType" value="file"> Upload CSV (name, phone, email)
        <input type="file" onchange="{{$onChange}}" name="{{ $fileInputName }}"  wire:model="file" class="shadow appearance-none border rounded w-full py-3 p-3 text-gray-700 focus:outline-none focus:shadow-outline" />
    </div>
</div>
