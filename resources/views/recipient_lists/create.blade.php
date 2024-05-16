<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Create Recipient List') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('clients.store') }}" enctype="multipart/form-data" method="post">
            @csrf
            <div class="mb-4">
              <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" >
            </div>
            <div class="mb-4">
              <livewire:toggle-input textarea-name="numbers" selector-name="entry_type" file-input-name="csv_file" />

              <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
              <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" >
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700  font-bold py-2 px-4 rounded">Create Client</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>