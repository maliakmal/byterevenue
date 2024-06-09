<div x-data="{ open: @entangle('showModal') }" x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50" style="display: none;">
    <div @click.away="open = false" class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <button @click="open = false" class="float-right text-gray-500 hover:text-gray-800">&times;</button>
        <div>
        {!! $content !!}
        </div>
    </div>
</div>
