<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Contacts') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <a href="{{ route('contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border  rounded-md font-semibold text-xs  uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">Add Contact</a>
          <table class="w-full mt-5">
            <thead>
              <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Phone</th>
                <th class="px-4 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($contacts as $contact)
                <tr>
                  <td class="border border-gray-200 px-4 py-2">{{ $contact->name }}</td>
                  <td class="border border-gray-200 px-4 py-2">{{ $contact->email }}</td>
                  <td class="border border-gray-200 px-4 py-2">{{ $contact->phone }}</td>
                  <td class="border border-gray-200 px-4 py-2">
                    <form action="{{ route('contacts.destroy', $contact->id) }}" method="post">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">Delete</button>
                    </form>
                    <a href="{{ route('contacts.edit', $contact->id) }}" class="inline-flex items-center px-2 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">Edit</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No contacts found') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          <br/>
          <div class="pagination">
            {{ $contacts->links() }}
          </div>

        </div>
      </div>
    </div>
  </div>
</x-app-layout>