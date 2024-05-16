<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Create Sim Card') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form method="POST" action="{{ route('sim_cards.store') }}">
            @csrf
            <div class="mb-6">
              <label for="number" class="block mb-2 text-sm text-gray-700">Number</label>
              <input type="text" name="number" id="number" value="{{ old('number') }}" required autofocus class="w-full px-3 py-2 placeholder-gray-400 border rounded-md focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 sm:text-sm">
              @error('number')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div class="mb-6">
              <label for="sms_capacity" class="block mb-2 text-sm text-gray-700">SMS Capacity</label>
              <input type="number" name="sms_capacity" id="sms_capacity" value="{{ old('sms_capacity') }}" required class="w-full px-3 py-2 placeholder-gray-400 border rounded-md focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 sm:text-sm">
              @error('sms_capacity')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div class="mb-6">
              <label for="country_code" class="block mb-2 text-sm text-gray-700">Country Code</label>
              <input type="text" name="country_code" id="country_code" value="{{ old('country_code') }}" required class="w-full px-3 py-2 placeholder-gray-400 border rounded-md focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 sm:text-sm">
              @error('country_code')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div class="mb-6">
              <label for="active" class="block mb-2 text-sm text-gray-700">Active</label>
              <select name="active" id="active" class="w-full px-3 py-2 placeholder-gray-400 border rounded-md focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 sm:text-sm">
                <option value="1" {{ old('active') == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('active') == 0 ? 'selected' : '' }}>No</option>
              </select>
              @error('active')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div class="mb-6">
              <label for="active_since" class="block mb-2 text-sm text-gray-700">Active Since</label>
              <input type="datetime-local" name="active_since" id="active_since" value="{{ old('active_since') }}" class="w-full px-3 py-2 placeholder-gray-400 border rounded-md focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 sm:text-sm">
              @error('active_since')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>
            <div class="flex items-center justify-end">
              <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-500">
                Create
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>