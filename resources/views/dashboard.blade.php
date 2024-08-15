<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        
        <!-- Welcome banner -->
        <x-dashboard.welcome-banner />

        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Avatars 
            <x-dashboard.dashboard-avatars />-->

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Filter button 
                <x-dropdown-filter align="right" />-->

                <!-- Datepicker built with flatpickr 
                <x-datepicker />-->

                <!-- Add view button 
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Filter</span>
                </button>-->
                
            </div>

        </div>
        <div class="">
        <form action="" id="form-admin-dashboard" class="float-right" method="post">
            @csrf
            @method('POST')
            <input type="text" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="dates" name="dates" value="" >
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text text-white font-bold py-2 px-4 rounded">Filter</button>
          </form>

        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold mb-1">Campaigns to Watch</h1>



          @if(auth()->user()->hasRole('admin'))
          <div class="mt-5">

          <div class="mt-5">
            <div class=" sm:rounded-lg">
              <ul role="list" class="grid grid-cols-4 gap-2">


              <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="flex w-full items-center justify-between space-x-6 p-6">
                  <div class="flex-1 truncate">
                    <div class="  ">
                      <h2 class="truncate  font-medium text-gray-900 text-3xl">
                        {{ $params['total_campaigns'] }}
                      </h2>
                      <p>Campaigns Created</p>
                    </div>
                  </div>
                </div>
              </li>
              <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="flex w-full items-center justify-between space-x-6 p-6">
                  <div class="flex-1 truncate">
                    <div class="  ">
                      <h2 class="truncate  font-medium text-gray-900 text-3xl">
                        {{ $params['total_num_sent'] }}
                      </h2>
                      <p>Sent messages</p>
                    </div>
                  </div>
                </div>
              </li>
              <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="flex w-full items-center justify-between space-x-6 p-6">
                  <div class="flex-1 truncate">
                    <div class="  ">
                      <h2 class="truncate  font-medium text-gray-900 text-3xl">
                        {{ $params['total_num_clicks'] }}
                      </h2>
                      <p>Clicks</p>
                    </div>
                  </div>
                </div>
              </li>
              <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="flex w-full items-center justify-between space-x-6 p-6">
                  <div class="flex-1 truncate">
                    <div class="  ">
                      <h2 class="truncate  font-medium text-gray-900 text-3xl">
                        {{ $params['ctr'] }}%
                      </h2>
                      <p>Click Through Rate</p>
                    </div>
                  </div>
                </div>
              </li>
              




              </ul>

            </div>
          </div>

          <div class="mt-5">
            <div class=" sm:rounded-lg">
            <div id="myLineChart" style="width: 100%; height: 400px;"></div>
            </div>
          </div>


          @else
          <div class="mt-5">
      <div class=" sm:rounded-lg">

        @if(count($campaigns)>0)
      <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

      @foreach ($campaigns as $campaign)
      <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
        <div class="flex w-full items-center justify-between space-x-6 p-6">
          <div class="flex-1 truncate">
            <div class="  ">
              <h2 class="truncate  font-medium text-gray-900">
              <a href="{{ route('campaigns.show', $campaign->id) }}" class="  text-gray-900">{{ $campaign->title }}</a></h2>
              <p>{{ $campaign->recipient_list ? $campaign->recipient_list->contacts()->count().' recipients':'-' }}</p>
              @if(auth()->user()->hasRole('admin'))
            <p class="">
              <a href="{{ route('accounts.show', $campaign->user_id) }}" class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
              </svg>{{ $campaign->user->name }}</a>
</p>
            @endif

            </div>
            <div class="flex-1 mt-4">
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

            </div>
          </div>
          <span class="inline-flex flex-shrink-0 items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-blue-600 ring-1 hidden ring-inset ring-green-600/20">Creator</span>      
        </div>
    <div>
    </div>
  </li>


          @endforeach
</ul>
@endif
</div>
      </div>
      @endif

      @if(auth()->user()->hasRole('admin'))
      <br/>
      <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-2">
      <li class="col-span-2 divide-y divide-gray-200 rounded-lg bg-white shadow">

  <div class="mt-5">
      <div class="  p-6 sm:rounded-lg">
        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold mb-1">Active Accounts</h1>

      <table  class="mt-5 table-auto ">
        <tr class="bg-gray-100">
        <tr class="bg-gray-100">
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Tokens</th>
              </tr>
            </thead>
            <tbody>
            @forelse ($accounts as $account)
                <tr>
                  <td class="border border-gray-200 px-4 py-2"><a class="text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('accounts.show', $account->id) }}">{{ $account->name.($account->hasRole('admin')?'(administrator)':'') }}</a></td>
                  <td class="border border-gray-200 px-4 py-2">{{ $account->email }}</td>
                  <td class="border border-gray-200 px-4 py-2">{{ Number::format($account->tokens) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No accounts found') }}</td>
                </tr>
              @endforelse


            </tbody></table>

      </div>
      </div>
</li>
<li class="col-span-2 divide-y divide-gray-200 rounded-lg bg-white shadow">
<div class="p-6 m-6">
<div class="p-6 m-6">
<div class="p-6 m-6">

                    <div class="text-3xl font-bold text-slate-800 dark:text-slate-100 mr-2">
                      <a class="text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400" href="/jobs">{{$params['total_not_downloaded_in_queue']}} / {{$params['total_in_queue']}}</a>
                    </div>
                <small>Messages in Queue</small>
                </div>
                </div>
                </div>

</li>
</ul>
      @endif

    </div>
        <!-- Cards -->

    </div>
</x-app-layout>
@push('scripts')
<script>
  $(function(){
    $('input[name="dates"]').daterangepicker({
      "startDate": '{{ $params['start_date'] }}',
      "endDate": '{{ $params['end_date'] }}'
    });
    $('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
      $('#form-admin-dashboard').submit();
    });
  });

  function generateDates(startDate, endDate) {
            const dates = [];
            let currentDate = new Date(startDate);

            while (currentDate <= endDate) {
                dates.push(new Date(currentDate)); // Add a copy of the current date
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return dates;
        }

        // Define the start and end dates for the three-month period
        const startDate = new Date('2024-06-01');
        const endDate = new Date('2024-08-31');

        // Generate dates for the X-axis
        const labels = generateDates(startDate, endDate);

        // Convert date objects to ISO string format for labels
        const labelsFormatted = labels.map(date => date.toISOString().split('T')[0]);

        // Dummy data for the four lines
        const dataLine1 = labels.map(() => Math.floor(Math.random() * 100));
        const dataLine2 = labels.map(() => Math.floor(Math.random() * 100));
        const dataLine3 = labels.map(() => Math.floor(Math.random() * 100));
        const dataLine4 = labels.map(() => Math.floor(Math.random() * 100));

        // Plotly data for the four lines
        const trace1 = {
            x: labelsFormatted,
            y: dataLine1,
            mode: 'lines',
            name: 'Line 1',
            line: {color: 'rgba(255, 99, 132, 1)'}
        };
        const trace2 = {
            x: labelsFormatted,
            y: dataLine2,
            mode: 'lines',
            name: 'Line 2',
            line: {color: 'rgba(54, 162, 235, 1)'}
        };
        const trace3 = {
            x: labelsFormatted,
            y: dataLine3,
            mode: 'lines',
            name: 'Line 3',
            line: {color: 'rgba(75, 192, 192, 1)'}
        };
        const trace4 = {
            x: labelsFormatted,
            y: dataLine4,
            mode: 'lines',
            name: 'Line 4',
            line: {color: 'rgba(153, 102, 255, 1)'}
        };

        // Plotly layout
        const layout = {
            title: '',
            xaxis: {
                title: 'Date',
                type: 'date'
            },
            yaxis: {
                title: 'Value'
            }
        };

        // Plot the chart
        Plotly.newPlot('myLineChart', [trace1, trace2, trace3, trace4], layout);

    </script>

