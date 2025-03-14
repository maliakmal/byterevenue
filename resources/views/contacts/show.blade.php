<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Contact Details') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">{{ $contact->name }}</h2>
            <p class="text-gray-700 mb-2">Email: {{ $contact->email }}</p>
            <p class="text-gray-700">Phone: {{ $contact->phone }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>