<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Sim Cards') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sim Cards</h2>
            </div>
            <div>
              <a href="{{ route('sim_cards.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-500">
                Create Sim Card
              </a>
            </div>
          </div>
          <table class="w-full table-auto">
            <thead>
              <tr>
                <th class="px-4 py-2">Number</th>
                <th class="px-4 py-2">SMS Capacity</th>
                <th class="px-4 py-2">Country Code</th>
                <th class="px-4 py-2">Active</th>
                <th class="px-4 py-2">Active Since</th>
                <th class="px-4 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($simCards as $simCard)
                <tr>
                  <td class="border px-4 py-2">{{ $simCard->number }}</td>
                  <td class="border px-4 py-2">{{ $simCard->sms_capacity }}</td>
                  <td class="border px-4 py-2">{{ $simCard->country_code }}</td>
                  <td class="border px-4 py-2">{{ $simCard->active ? 'Yes' : 'No' }}</td>
                  <td class="border px-4 py-2">{{ $simCard->active_since }}</td>
                  <td class="border px-4 py-2">
                    <div class="flex items-center justify-center">
                      <a href="{{ route('sim_cards.edit', $simCard->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring focus:ring-green-200 focus:border-green-500">
                        Edit
                      </a>
                      <form action="{{ route('sim_cards.destroy', $simCard->id) }}" method="post" class="ml-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring focus:ring-red-200 focus:border-red-500">
                          Delete
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>