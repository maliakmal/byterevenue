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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Campaigns</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Campaigns</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

          <span class="ml-3 hidden sm:block">
            <a href="{{ route('campaigns.create') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
<svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
  <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
</svg>


              New Campaign
</a>
          </span>


        </div>
      </div>
    </header>

  <div class="py-12">

  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
  @include('partials.alerts')
  <div class=" sm:rounded-lg">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
@if(count($campaigns)>0)
<div>
  <form method="get" id="filter-form">
  <select id="status" name="status">
  <option value="">All Status</option>
  <option {{ $filter['status']==0?'selected' :'' }} value="0">Draft</option>
  <option {{ $filter['status']==1?'selected' :'' }}  value="1">Processing</option>
  <option {{ $filter['status']==2?'selected' :'' }}  value="2">Done</option>
</select>
@if(auth()->user()->hasRole('admin'))
<select id="user_id" name="user_id">
  <option  value="">All Accounts?</option>
  @foreach(\App\Models\User::select()->orderby('name', 'asc')->get() as $user)
    <option {{ $filter['user_id']==$user->id?'selected' :'' }}  value="{{ $user->id }}">{{ $user->name }}</option>
  @endforeach
</select>
@endif
<select id="sortby" name="sortby">
  <option value="">Sort By</option>
  <option  {{ $filter['sortby']=='id_desc'?'selected' :'' }}  value="id_desc">Latest to Oldest</option>
  <option  {{ $filter['sortby']=='id_asc'?'selected' :'' }}  value="id_asc">Oldest to Latest</option>
  <option  {{ $filter['sortby']=='title'?'selected' :'' }}  value="title">Title - Alphabetically</option>
</select>
<select id="count" name="count">
  <option value="">Count</option>
  <option  {{ $filter['count']=='5'?'selected' :'' }}  value="5">5</option>
  <option  {{ $filter['count']=='10'?'selected' :'' }}  value="10">10</option>
  <option  {{ $filter['count']=='50'?'selected' :'' }}  value="50">50</option>
  <option  {{ $filter['count']=='100'?'selected' :'' }}  value="100">100</option>
</select>
</form>
</div>
<table  class="mt-5 table-auto w-full">
        <thead>
        <tr class="bg-gray-100">
        <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Title</th>
                @if(auth()->user()->hasRole('admin'))
                <th class="px-4 py-2">Account</th>
                @endif

                <th class="px-4 py-2">Recipients</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Created At</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>

      @foreach ($campaigns as $campaign)

      <tr>
                <td class="border-b border-gray-200 px-4 py-2">{{ $campaign->id }}</td>
                <td class="border-b border-gray-200 px-4 py-2">{{ $campaign->title }}</td>

                @if(auth()->user()->hasRole('admin'))
                <td class="border-b border-gray-200 px-4 py-2"><a href="{{ route('accounts.show', $campaign->user_id) }}" class="flex items-center">
                {{ $campaign->user->name }}</a></td>
                @endif
                <td class="border-b border-gray-200 px-4 py-2">{{ $campaign->recipient_list ? $campaign->recipient_list->contacts->count():'-' }}</td>
                <td class="border-b border-gray-200 px-4 py-2">
                @switch($campaign->status)
                  @case(\App\Models\Campaign::STATUS_DRAFT)
                    <span class="py-1 px-2.5 border-none rounded bg-blue-100  text-blue-800 font-medium">Draft</span>
                  @break
                  @case(\App\Models\Campaign::STATUS_PROCESSING)
                    <span class="py-1 px-2.5 border-none rounded bg-yellow-100  text-yellow-800 font-medium">Processing</span>
                  @break
                  @case(\App\Models\Campaign::STATUS_PROCESSING)
                    <span class="py-1 px-2.5 border-none rounded bg-green-100  text-green-800 font-medium">Done</span>
                    @break
                  @endswitch                  

                </td>
                <td class="border-b border-gray-200 px-4 py-2">{{ $campaign->created_at }}</td>
                <td class="border-b border-gray-200 px-4 py-2">
                  
                <div class="-mt-px flex divide-x divide-gray-200">
    <div class="flex w-0 flex-1">
          <a href="{{ route('campaigns.show', $campaign->id) }}" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border-none py-4 text-sm font-semibold text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
  <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
  <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
</svg>
            
          </a>
        </div>
        <div class="flex w-0 flex-1">
          <a href="{{ route('campaigns.edit', $campaign->id) }}" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3  py-4 text-sm font-semibold text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
  <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" />
</svg>
            
          </a>
        </div>
        <div class="flex w-0 flex-1">
        <form action="{{ route('campaigns.destroy', $campaign->id) }}" method="post" class="relative -ml-px inline-flex w-0 flex-1 items-center justify-center">
          @csrf
          @method('DELETE')
          <button type="submit"  class="w-full flex items-center justify-center gap-x-3   py-4 text-sm font-semibold text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
              <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
            </svg>
          
          </button>
        </form>          
        </div>
      </div>

                </td>
            </tr>




          @endforeach
            <!-- Add more rows as needed -->
            </tbody>
    </table>
<br/>
{{ $campaigns->appends($filter)->links()}}
@else
<div class="flex w-full items-center  space-x-6 p-6">
  <div class="flex-1 truncate">
    <div class="flex items-center space-x-3">
      <h3 class="truncate text-sm font-medium text-gray-900">No Campaigns found</h3>
      
    </div>
  </div>
  <span class="inline-flex flex-shrink-0 items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-blue-600 ring-1 hidden ring-inset ring-green-600/20">Creator</span>      
</div>

@endif

      
      
</div>
    </div>
    </div>
    </div>
  </div>
</x-app-layout>
@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function() {
  var selectElements = document.querySelectorAll('#filter-form select');
  var form = document.getElementById('filter-form');

  selectElements.forEach(function(selectElement) {
    selectElement.addEventListener('change', function() {
      if (form) {
        form.submit();
      }
    });
  });
});


</script>