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
<svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
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
<div>
  <form method="get" id="filter-form">
  <select id="filter_status" name="status">
  <option {{ $filter['status']==''?'selected' :'' }} value="">All Status</option>
  <option {{ $filter['status']=='0'?'selected' :'' }} value="0">Draft</option>
  <option {{ $filter['status']==1?'selected' :'' }}  value="1">Processing</option>
  <option {{ $filter['status']==2?'selected' :'' }}  value="2">Done</option>
</select>
@if(auth()->user()->hasRole('admin'))
<select id="filter_user_id" name="user_id">
  <option  value="">All Accounts?</option>
  @foreach(\App\Models\User::select()->orderby('name', 'asc')->get() as $user)
    <option {{ $filter['user_id']==$user->id?'selected' :'' }}  value="{{ $user->id }}">{{ $user->name }}({{ $user->campaigns()->count() }})</option>
  @endforeach
</select>
@endif
<select id="filter_sortby" name="sortby">
  <option value="">Sort By</option>
  <option  {{ $filter['sortby']=='id_desc'?'selected' :'' }}  value="id_desc">Latest to Oldest</option>
  <option  {{ $filter['sortby']=='id_asc'?'selected' :'' }}  value="id_asc">Oldest to Latest</option>
  <option  {{ $filter['sortby']=='title'?'selected' :'' }}  value="title">Title - Alphabetically</option>
  <option  {{ $filter['sortby']=='ctr_desc'?'selected' :'' }}  value="ctr_desc">CTR - Descending</option>
  <option  {{ $filter['sortby']=='ctr_asc'?'selected' :'' }}  value="ctr_asc">CTR - Ascending</option>
  <option  {{ $filter['sortby']=='clicks_desc'?'selected' :'' }}  value="clicks_desc">Total Clicks - Descending</option>
  <option  {{ $filter['sortby']=='clicks_asc'?'selected' :'' }}  value="clicks_asc">Total Clicks - Ascending</option>
</select>

</form>
</div>
      <ul id="list-container" role="list" class="divide-y divide-gray-100">
      @foreach ($campaigns as $campaign)

              <li class="flex justify-between gap-x-6 py-5">
                  <div class="flex min-w-0 gap-x-4">
                      <img style="border-radius: 50%" class="h-12 w-12 flex-none bg-gray-50"  src="/images/campaignkoochool.png" alt="">
                      <div class="min-w-0 flex-auto">
                          <p class="text-sm font-semibold leading-6 text-gray-900"> {{ $campaign->title  ?? "{NO NAME}"}}</p>
                          <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                              @if(auth()->user()->hasRole('admin'))
                                  <a href="{{ route('accounts.show', $campaign->user_id) }}" class="flex text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 items-center">
                                      {{ $campaign->user?->name }}
                                  </a>
                              @endif
                          </p>
                      </div>
                  </div>
                  <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                      <div class="mt-1 flex items-center gap-x-1.5">
                          <div class="mt-1 flex items-center gap-x-1.5">
                              <div class="flex-none rounded-full @if($campaign->status == \App\Models\Campaign::STATUS_DONE) bg-emerald-500/20  @else bg-yellow-100 @endif p-1">
                                  <div class="h-1.5 w-1.5 rounded-full @if($campaign->status == \App\Models\Campaign::STATUS_DONE)  bg-emerald-500 @else  bg-yellow-500  @endif"></div>
                              </div>
                              <p class="text-xs leading-5 text-gray-500">{{$campaign->statusString()}}</p>
                          </div>

                          <span class="text-xs">|</span>
                          <div class="mt-1 flex items-center gap-x-1.5">
                              <a href="{{route('campaigns.show', $campaign->id) }}" style="color: green" class="text-xs leading-5 text-gray-500">SHOW</a>
                          </div>
                          <span class="text-xs">|</span>
                          <div class="mt-1 flex items-center gap-x-1.5">
                              <a href="{{route('campaigns.edit', $campaign->id) }}" style="color: dodgerblue" class="text-xs leading-5 text-gray-500">EDIT</a>
                          </div>
                          @if($campaign->canBeDeleted())
                              <span class="text-xs">|</span>

                          <form method="post" action="{{route('campaigns.destroy', $campaign->id) }}">
                              <div class="mt-1 flex items-center gap-x-1.5">
                                  @csrf
                                  @method('DELETE')
                                  <button class="text-xs leading-5 " style="color:darkred"  href="{{route('recipient_lists.destroy', $campaign->id) }}">DELETE</button>
                              </div>
                          </form>
                          @endif
                      </div>
                      <p class="mt-1 text-xs leading-5 text-gray-500">Total Click: {{number_format($campaign->total_recipients_click_thru)}} | Total Sent: {{number_format($campaign->total_recipients_sent_to)}} |  Total Recipients: {{ number_format($campaign->total_recipients) }}
                          | Total CTR  {{ number_format($campaign->total_ctr, 2) }}%
                      </p>
                  </div>
              </li>
          @endforeach
      </ul>
</div>
          <div style="margin-top: 20px">
              <button id="loadDataButton" style="margin: 0 auto" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                  Load More ...
              </button>
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
var page = 1;

$(document).ready(function() {
    var myEl = document.getElementById('loadDataButton');
    myEl.addEventListener('click', function () {
        $.LoadingOverlay("show");
        // var area_code = $('#city-filter').val();
        // var phone = $('#phone').val();
        var status = $('#filter_status').val();
        var user_id = $('#filter_user_id').val() ?? '';
        var sort = $('#filter_sortby').val();
        page++;
        var url = "{!! route('campaigns.index') !!}" + "?output=json&page=" + page + "&status=" + status+ "&user_id=" + user_id+ "&sortby=" + sort ;
        $.get(url, function (data) {
            if (data.data.last_page <= page) {
                $('#loadDataButton').remove();
            }
            var elements = data.data.data;
            var str = "";
            var i = 0;
            for (i = 0; i < elements.length; i++) {
                str += getElementString(elements[i]);
            }
            $('#list-container').append(str);
            $.LoadingOverlay("hide");

        });

    });

    function statusString(status)
    {
        if(status == '0'){
            return 'Draft';
        }
        if(status == '1'){
            return 'Processing';
        }
        if(status == '2'){
            return 'Done';
        }
        return "";
    }
    function getElementString(ele){
        var edit = "{!!  route('campaigns.edit', 'id') !!}" ;
        var del = "{!!  route('campaigns.destroy', 'id') !!}" ;
        var show = "{!!  route('campaigns.show', 'id') !!}" ;
        var campaignString = statusString(ele.status);
        edit = edit.replace('id', ele.id);
        del = del.replace('id', ele.id);
        show = show.replace('id', ele.id);
        var showUser = '';

        @if(auth()->user()->hasRole('admin'))
         showUser = "{!! route('accounts.show', 'id') !!}" ;
         showUser = showUser.replace('id', ele.id);
        @endif


        return `
         <li class="flex justify-between gap-x-6 py-5">
                  <div class="flex min-w-0 gap-x-4">
                      <img style="border-radius: 50%" class="h-12 w-12 flex-none bg-gray-50"  src="/images/campaignkoochool.png" alt="">
                      <div class="min-w-0 flex-auto">
                          <p class="text-sm font-semibold leading-6 text-gray-900"> ${ele.title || "{NO NAME}"} </p>
                          <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                              @if(auth()->user()->hasRole('admin'))
        <a href="${showUser}" class="flex text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 items-center">
            ${ele?.user?.name || ''}
        </a>
@endif
        </p>
    </div>
</div>
<div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
    <div class="mt-1 flex items-center gap-x-1.5">
        <div class="mt-1 flex items-center gap-x-1.5">
            <div class="flex-none rounded-full  ${ele.status == {!! \App\Models\Campaign::STATUS_DONE !!} ? 'bg-emerald-500/20' : 'bg-yellow-100' } p-1">
                                  <div class="h-1.5 w-1.5 rounded-full ${ele.status == {!! \App\Models\Campaign::STATUS_DONE !!} ? 'bg-emerald-500' : 'bg-yellow-500' }"></div>
                              </div>
                              <p class="text-xs leading-5 text-gray-500">${campaignString}</p>
                          </div>

                          <span class="text-xs">|</span>
                          <div class="mt-1 flex items-center gap-x-1.5">
                              <a href="${show}" style="color: green" class="text-xs leading-5 text-gray-500">SHOW</a>
                          </div>
                          <span class="text-xs">|</span>
                          <div class="mt-1 flex items-center gap-x-1.5">
                              <a href="${edit}" style="color: dodgerblue" class="text-xs leading-5 text-gray-500">EDIT</a>
                          </div>
                          <span  style="color:darkred;display:${ele.status == '0' ? 'block' : 'none'}" class="text-xs">|</span>

        <form method="post" action="${del}">
                              <div class="mt-1 flex items-center gap-x-1.5">
                                  @csrf
        @method('DELETE')
        <button class="text-xs leading-5 " style="color:darkred;display:${ele.status == '0' ? 'block' : 'none'}" >DELETE</button>
                              </div>
                          </form>

        </div>
        <p class="mt-1 text-xs leading-5 text-gray-500">Total Click: ${parseFloat(ele.total_recipients_click_thru).toFixed(2)} | Total Sent: ${parseFloat(ele.total_recipients_sent_to).toFixed(2)} |  Total Recipients: ${parseFloat(ele.total_recipients).toFixed(2)}
        | Total CTR  ${parseFloat(ele.total_ctr).toFixed(2)}%
                      </p>
                  </div>
              </li>

              `;

    }

});

</script>
