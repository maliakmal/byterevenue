<x-app-layout>
<header class="bg-gray-50 py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 xl:flex xl:items-center xl:justify-between">
        <div class="min-w-0 flex-1">
          <nav class="flex" aria-label="Breadcrumb">
            <ol role="list" class="flex items-center space-x-4">
              <li>
                <div>
                  <a href="/" class="text-sm font-medium text-gray-500 hover:text-gray-700">Dashboard</a>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
</svg>
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Recipient Lists</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ $recipientsList->name }}</h1>
          @if($recipientsList->is_imported)
          <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">Import Complete</span>

          @else
          <span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-yellow-300 border border-yellow-300">Import in Progress</span>
          @endif
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

          <span class="ml-3 hidden sm:block">
            <a href="{{ route('recipient_lists.create') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
<svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
  <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
</svg>


              
</a>
          </span>


        </div>
      </div>
    </header>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden ">
        <div class="p-6 bg-white border-b 200">
          <div class="bg-white  rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4"></h2>
            <table class="w-full table-fixed">
            <thead>
              <tr class="bg-gray-100">
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Name</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Email</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Phone</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase"></th>
              </tr>

            </thead>
            <tbody>
              @forelse ($contacts  as $contact)
                <tr>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $contact->name }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $contact->email }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $contact->phone }}</td>
                  <td class="py-4 px-6 border-b border-gray-200"></td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No contacts found') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
<br/>
          {{$contacts->links()}}

          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>