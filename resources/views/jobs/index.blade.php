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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Jobs</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Jobs</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

        </div>
      </div>
    </header>


  <div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    @if($params['download_me']!= null)
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-3" role="alert">
              <span class="block sm:inline">Export file successfully generated - <a href="{{ $params['download_me'] }}" target="_blank">click here</a> to download</span>
              <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';"><title>Close</title><path d="M14.348 14.849a1 1 0 001.415-1.414l-4.829-4.829 4.829-4.829A1 1 0 0014.348 2.93l-4.829 4.829-4.829-4.829A1 1 0 102.93 4.606l4.829 4.829-4.829 4.829a1 1 0 101.414 1.414l4.829-4.829 4.829 4.829z"/></svg>
              </span>
          </div>
    @endif
  </div>

  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="col-span-2 divide-y divide-gray-200 rounded-lg bg-white shadow">
        <div class="p-6 m-6">
          <div class="p-6 m-6">
            <div class="p-6 m-6">
              <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mr-2">{{$params['total_not_downloaded_in_queue']}} / {{$params['total_in_queue']}}</div>                
              <small>Messages in Queue</small>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-5 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('jobs.postIndex') }}?_h={{ time() }}" enctype="multipart/form-data" method="post">
              @csrf
              <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Generate Message Exports</div>
              <p>This would generate a csv of deliverable messages which can be downloaded from the table below.</p>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Number of messages</label>
                <select id="number_messages" name="number_messages" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach([50, 100, 250, 500, 1000, 1500, 2000, 5000, 10000, 20000, 30000] as $num)
                  <option value="{{$num}}">{{$num}} messages</option>
                @endforeach
                </select>
              </div>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Short Domains</label>
                <select id="url_shortener" name="url_shortener" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach($params['urlShorteners'] as $vv)  
                <option value="{{ $vv->name }}">{{ $vv->name }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">Download</button>
            </form>
          <br/>
          </div>
          </div>
      
      <div class="mt-5 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">

          <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Downloadable files</div>
      <table  class="mt-5 downloadables table-auto w-full">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2">Filename</th>
            <th class="px-4 py-2">No. Entries</th>
            <th class="px-4 py-2">Created At</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($params['files'] as $file)
              <tr>
                <td class="border-b border-gray-200 px-4 py-2">
                  <a href="/download/{{$file['id'] }}">{{ $file['filename'] }}</a> 
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                {{ $file['number_of_entries'] }}
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  <a href="/download/{{$file['id'] }}">
                  @if($file['is_ready'] == 1)
                    (pending)
                  @endif
                    {{ $file['created_at']->diffForHumans() }}
                  </a> 
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <br/>
        {{ $params['files']->links() }}

        </div>
      </div>
    </div>
  </div>
</x-app-layout>

@push('scripts')
<script>
    @if($params['download_me']!= null)
    $('.downloadables:first li:first-child').toggle( "highlight" );
// function downloadURI(uri, name) {
//   var link = document.createElement("a");
//   link.download = name; // <- name instead of 'name'
//   link.href = uri;

//   link.click();
//   link.remove();
// }

// downloadURI('{{ $params['download_me'] }}', '{{ $params['download_me'] }}');


    @endif
</script>
