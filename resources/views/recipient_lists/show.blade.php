<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Recipient List Details') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">{{ $recipientsList->name }}</h2>
            <p class="text-gray-700 mb-2">Contacts</p>
            <table class="w-full mt-5">
            <thead>
              <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Phone</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recipientsList->contacts() as $contact)
                <tr>
                  <td class="border border-gray-200 px-4 py-2">{{ $contact->name }}</td>
                  <td class="border border-gray-200 px-4 py-2">{{ $contact->email }}</td>
                  <td class="border border-gray-200 px-4 py-2">{{ $contact->phone }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No contacts found') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>

          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>