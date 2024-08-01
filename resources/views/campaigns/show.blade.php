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
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Campaign: {{ $campaign->title }}     @switch($campaign->status)
                  @case(\App\Models\Campaign::STATUS_DRAFT)
                    <span class="py-1 px-2.5 border-none rounded bg-blue-100 text-xl text-blue-800 font-medium">Draft</span>
                  @break
                  @case(\App\Models\Campaign::STATUS_PROCESSING)
                    <span class="py-1 px-2.5 border-none rounded bg-yellow-100 text-xl text-yellow-800 font-medium">Processing</span>
                  @break
                  @case(\App\Models\Campaign::STATUS_PROCESSING)
                    <span class="py-1 px-2.5 border-none rounded bg-green-100 text-xl text-green-800 font-medium">Done</span>
                    @break
                  @endswitch
                  </h1> 
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
            {{ $campaign->description }}
          </div>
          @if(auth()->user()->hasRole('admin'))
          <p class="text-gray-700"><b>Unique Folder:</b> {{ $campaign->getUniqueFolder() }}</p>
          @endif

          <p class="text-gray-700"><b>Recipient List:</b> {{ $campaign->recipient_list->name }}({{  $campaign->recipient_list->contacts()->count() }} contacts)</p>
          <p class="{{ $campaign->user->tokens >= $campaign->recipient_list->contacts()->count() ? 'text-green-700' : 'text-red-700'}}"><b>Available Tokens:<b>{{ $campaign->user->tokens}}</p>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

        @if($campaign->canBeDeleted())
        <span class="ml-3  sm:block">
                <a href="{{ route('campaigns.markProcessed', ['id'=>$campaign->id]) }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
                    <svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
                    </svg>
                    Process
                    </a>
                </span>

        <span class="ml-3  sm:block">
          <a href="{{ route('campaigns.edit', $campaign->id) }}?campaign_id={{ $campaign->id }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
            <svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
              <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
            </svg>
            Edit
            </a>
          </span>

          <span class="ml-3  sm:block">
          <span class="flex items-center justify-start text-gray-500">
            <form action="{{ route('campaigns.destroy', $campaign->id) }}" method="post">
              @csrf
              @method('DELETE')
              <button type="submit" onclick="return confirm('Are you sure')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
                <span>
                  <svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </span>
                <span class=" md:inline-block">Delete</span>
              </button>
            </form>


          </span>
          @endif



        </div>
      </div>

    </header>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
  @include('partials.alerts')
                  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">


        @if($campaign->isDispatched())
      <div>
        <table class="w-full table-fixed">
            <thead>
              <tr class="bg-gray-100">
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Phone</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Message</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Processed At</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Status</th>
              </tr>

            </thead>
            <tbody>
              @forelse ($logs  as $log)
                <tr>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $log->recipient_phone }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">
                  
                    <div class="mr-auto rounded-lg rounded-tl-none my-1 p-2 text-sm bg-white flex flex-col relative speech-bubble-left">
                      <p>{{ $log->message_body }}</p>
                    </div>
                  
                  
                  </td>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $log->created_at }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">UNDER PROCESS</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No logs found') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>


          </div><br/>
          {{$logs->links()}}

          @else
          <div>
        <table class="w-full table-fixed">
            <thead>
              <tr class="bg-gray-100">
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Contact</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Message</th>
              </tr>

            </thead>
            <tbody>
              @forelse ($contacts as $contact)
                <tr>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $contact->phone }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">
                  <div class="mr-auto rounded-lg rounded-tl-none my-1 p-2 text-sm bg-blue-100 flex flex-col relative speech-bubble-left">
                      <p>{{ $message->getParsedMessage() }}</p>
                    </div>
 
                  </td>
                  <td class="py-4 px-6 border-b border-gray-200"></td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No contacts found') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>


          </div>
          <br/>
          {{$contacts->links()}}

          @endif








        </div>
      </div>


    </div>
  </div>
</x-app-layout>