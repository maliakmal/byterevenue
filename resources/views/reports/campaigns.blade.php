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
                        <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Campaigns</a>
                    </div>
                </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Campaigns Report</h1>
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

  <form method="get" id="filter-form" action="/reports/campaigns">
      <input type="hidden" id="download_csv" name="download_csv" value="0"/>
      <div class="flex mb-4">
      <div class="w-1/3 h-12">
          <label>By Client?</label>
          <select  name="user_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="user_filter">
              <option value="">choose</option>
              @foreach($users as $user)
                  <option value="{{$user->id}}" @if(($_GET['user_id'] ?? null) == $user->id) selected @endif>{{$user->name}}</option>
              @endforeach
          </select>
      </div>

      </div>
      <div class="h-12">
          <button type="button" onclick="download_csv.value=0;document.getElementById('filter-form').submit()"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
              Filter
          </button>
          <button type="button" onclick="download_csv.value=1;document.getElementById('filter-form').submit()" class="bg-green-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
              Export CSV
          </button>
      </div>
</form>

<table  class="mt-5 table-auto w-full">
        <thead>
        <tr class="bg-gray-100">
                <th class="px-4 py-2">Campaign ID</th>
                <th class="px-4 py-3">Campaign Name</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Number Sent</th>
                <th class="px-4 py-3">Number UnSent</th>
                <th class="px-4 py-3">Number Clicked</th>
                <th class="px-4 py-3">Number Not Clicked</th>
                <th class="px-4 py-3">Total Message</th>
                <th class="px-4 py-3">CTR</th>
                <th class="px-4 py-3">Last Updated</th>

            </tr>
        </thead>
        <tbody>
        @if(count($list)>0)
      @foreach ($list as $item)

      <tr>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->id }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->title }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->user?->name }} ({{ $item->user?->id }})</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->broad_case_log_messages_sent_count }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->broad_case_log_messages_un_sent_count }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->broad_case_log_messages_click_count }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->broad_case_log_messages_not_click_count }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->broad_case_log_messages_count }}</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">@if($item->broad_case_log_messages_sent_count > 0) {{ ($item->broad_case_log_messages_click_count /  $item->broad_case_log_messages_sent_count) * 100 }} @else 0 @endif</td>
                <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->updated_at }}</td>

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

</script>
