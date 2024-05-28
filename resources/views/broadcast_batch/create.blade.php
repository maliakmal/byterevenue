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
                  <a href="/campaigns" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Campaigns</a>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
                  </svg>
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Campaign: {{ $campaign->title }}</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Campaign: {{ $campaign->title }}</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
            {{ $campaign->description }}
          </div>
          <p class="text-gray-700">Client: {{ $campaign->client->name }}</p>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">
        <span class="ml-3 hidden sm:block">
          <a href="{{ route('broadcast_batches.create') }}?campaign_id={{ $campaign->id }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
            <svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
              <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
            </svg>
            Create a Broadcast Job
            </a>
          </span>


        </div>
      </div>
    </header>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('broadcast_batches.store') }}" method="post">
            @csrf
            <div class="mb-4">
              <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Recipient List</label>
              <select id="recipients_list_id" name="recipients_list_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                <option value="">Select an option</option>
                @foreach ($recipient_lists as $recipient_list)
                    <option value="{{ $recipient_list->id }}">{{ $recipient_list->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-4">
              <label for="message_subject" class="block text-gray-700 text-sm font-bold mb-2">Message Subject</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="message_subject" name="message_subject" >
              <input type="hidden"  id="campaign_id" name="campaign_id" value="{{ $campaign->id }}" >
            </div>
            <div class="mb-4">
              <label for="message_body" class="block text-gray-700 text-sm font-bold mb-2">Message Body</label>
              <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="message_body" name="message_body" ></textarea>
            </div>
            <div class="mb-4">
              <label for="message_target_url" class="block text-gray-700 text-sm font-bold mb-2">Message Target Url</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="message_target_url" name="message_target_url" >
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700  font-bold py-2 px-4 rounded">Save</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>