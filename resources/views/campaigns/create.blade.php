<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Create Contact') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('campaigns.store') }}" method="post">
            @csrf
            <div class="mb-4">
              <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="title" name="title" >
            </div>
            <div class="mb-4">
              <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
              <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" ></textarea>
            </div>
            <div class="mb-4">
              <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Client</label>
              <select id="client_id" name="client_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                <option value="">Select an option</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700  font-bold py-2 px-4 rounded">Create Contact</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>