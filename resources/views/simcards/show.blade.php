<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Sim Card Details') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sim Card Details</h2>
            </div>
            <div>
              <a href="{{ route('sim_cards.edit', $simCard->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-500">
                Edit
              </a>
            </div>
          </div>
          <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
              <label for="number" class="block text-gray-700 text-sm font-bold mb-2">Number</label>
              <p class="text-gray-600 text-sm">{{ $simCard->number }}</p>
            </div>
            <div class="mb-4">
              <label for="sms_capacity" class="block text-gray-700 text-sm font-bold mb-2">SMS Capacity</label>
              <p class="text-gray-600 text-sm">{{ $simCard->sms_capacity }}</p>
            </div>
            <div class="mb-4">
              <label for="country_code" class="block text-gray-700 text-sm font-bold mb-2">Country Code</label>
              <p class="text-gray-600 text-sm">{{ $simCard->country_code }}</p>
            </div>
            <div class="mb-4">
              <label for="active" class="block text-gray-700 text-sm font-bold mb-2">Active</label>
              <p class="text-gray-600 text-sm">{{ $simCard->active ? 'Yes' : 'No' }}</p>
            </div>
            <div class="mb-4">
              <label for="active_since" class="block text-gray-700 text-sm font-bold mb-2">Active Since</label>
              <p class="text-gray-600 text-sm">{{ $simCard->active_since }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>