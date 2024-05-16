<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Edit Contact') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('contacts.update', $contact->id) }}" method="post">
            @csrf
            @method('PUT')
            <div class="mb-4">
              <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" value="{{ $contact->name }}" required>
            </div>
            <div class="mb-4">
              <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
              <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" value="{{ $contact->email }}" required>
            </div>
            <div class="mb-4">
              <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" value="{{ $contact->phone }}" required>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text font-bold py-2 px-4 rounded">Update Contact</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>