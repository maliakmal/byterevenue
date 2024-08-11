<style>
    #filter-form  div{
        padding: 3px;
    }
</style>
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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Reports</a>
                </div>
              </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Messages</a>
                    </div>
                </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Broadcast Log Message</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
      </div>
    </header>

  <div class="py-12">

  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
  @include('partials.alerts')
  <div class=" sm:rounded-lg">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

  <form method="get" id="filter-form">
      <div class="flex mb-4">
      <div class="w-1/3 h-12">
          <label>user</label>
          <select onchange="userChange(this)" name="user_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="user_filter">
              <option value="">choose</option>
              @foreach($users as $user)
                  <option value="{{$user->id}}" @if(($_GET['user_id'] ?? null) == $user->id) selected @endif>{{$user->name}}</option>
              @endforeach
          </select>
      </div>
          <div class="w-1/3 h-12">
              <label>campaign</label>
              <select name="campaign_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="campaign_filter">
                  <option value="">choose</option>
                  @foreach($campaigns as $campaign)
                      <option value="{{$campaign->id}}"  @if(($_GET['campaign_id'] ?? null) == $campaign->id) selected @endif>{{$campaign->title}}</option>
                  @endforeach
              </select>
          </div>
          <div class="w-1/3 h-12">
              <label>status</label>
              <select name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="status_filter">
                  <option value="">choose</option>
                  <option value="send"  @if(($_GET['status'] ?? null) == 'send') selected @endif>send</option>
                  <option value="unsend"  @if(($_GET['status'] ?? null) == 'unsend') selected @endif>unsend</option>
              </select>
          </div>
          <div class="w-1/3 h-12">
              <label>click</label>
              <select name="is_click" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="click_filter">
                  <option value="">choose</option>
                  <option value="1"  @if(($_GET['is_click'] ?? null) == '1') selected @endif>clicked</option>
                  <option value="0"  @if(($_GET['is_click'] ?? null) == '0') selected @endif>none</option>
              </select>
          </div>
      </div>
      <div class="h-12">
          <button  class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
              search
          </button>
      </div>

</form>

<table  class="mt-5 table-auto w-full">
        <thead>
        <tr class="bg-gray-100">
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-3">campaign id</th>
                <th class="px-4 py-3">campaign name</th>
                <th class="px-4 py-3">is sent</th>
                <th class="px-4 py-3">sent at</th>
                <th class="px-4 py-3">is_click</th>
                <th class="px-4 py-3">clicked at</th>
                <th class="px-4 py-3">user id</th>
                <th class="px-4 py-3">user</th>
                <th class="px-4 py-3">status</th>

            </tr>
        </thead>
        <tbody>
        @if(count($list)>0)
      @foreach ($list as $item)

      <tr>

                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->id }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->campaign?->id }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->campaign?->title }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->is_sent }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->sent_at }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->is_click }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->click_at }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->campaign?->user?->id }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->campaign?->user?->name }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->status }}</td>
            </tr>

          @endforeach
            <!-- Add more rows as needed -->
            </tbody>
    </table>
<br/>
{{ $list->appends($filter)->links()}}
@else
<div class="flex w-full items-center  space-x-6 p-6">
  <div class="flex-1 truncate">
    <div class="flex items-center space-x-3">
      <h3 class="truncate text-sm font-medium text-gray-900">No Settings found</h3>

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

    function userChange(element){
        document.getElementById('campaign_filter').innerHTML = '';
        var id = element.value;
        if(id){
            document.getElementById('campaign_filter').innerHTML = "<option value=''>loading.... </option>";
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "/user/campaigns?user_id="+id);
            xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");
            const body = JSON.stringify({
                userId: 1,
                title: "Fix my bugs",
                completed: false
            });
            xhr.onload = () => {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = (JSON.parse(xhr.responseText)).data;
                    fillCampaign(data);
                } else {
                    console.log(`Error: ${xhr.status}`);
                }
            };
            xhr.send(body);
        }

    }
    function fillCampaign(array){
        if(array){
            var i;
            var data = "<option value=''>choose</option>";
            for(i=0;i<array.length;i++){
                var option = "<option value="+array[i].id+">"+ array[i].title+"</option>"
                data += option;
            }
            document.getElementById('campaign_filter').innerHTML = data;
        }

    }

</script>
