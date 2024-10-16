<x-app-layout>
  <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto">
    <!-- Welcome banner -->
    <x-dashboard.welcome-banner />
    <!-- Dashboard actions -->
    @if(auth()->user()->hasRole('admin') == false && auth()->user()->show_introductory_screen == true)
      <section class="bg-gray-50 ">
        <div class="mx-auto max-w-7xl ">
          <div class="grid grid-cols-2">
          <div class="w-full mb-4 p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
              <div>
              </div>
              <div class="ml-6">
                  <h3
                      class="text-xl font-bold text-gray-900 before:mb-2 before:block before:font-mono before:text-sm before:text-gray-500">
                      Import Reception List
                  </h3>
                  <h4 class="mt-2 text-base text-gray-700">Import a Recipient List to build a list of contacts</h4>
              </div>
              <div style="margin-top: 20px">
                <a href="/recipient_lists/create" class="w-full text-white @if(!$has_reception_list) bg-blue-700 @else  bg-gray-500 @endif hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Create Reception List</a>
              </div>
            </div>

            <div class="w-full  p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
              <div>
                <div class="ml-6 lg:ml-0 lg:mt-10">
                  <h3
                      class="text-xl font-bold text-gray-900 before:mb-2 before:block before:font-mono before:text-sm before:text-gray-500">
                      Create Campaign
                  </h3>
                  <h4 class="mt-2 text-base text-gray-700"> Create your first campaign here</h4>
                </div>
              </div>
              <div style="margin-top: 20px">
                <a href="/campaigns/create" class="w-full text-white @if(!$has_campaign) bg-blue-700 @else  bg-gray-500 @endif  hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Create campaign</a>
              </div>
            </div>
          </div>
        </div>
      </section>
      <br/>
      <br/>
      <a href="/introductory/disable" style="float: right;margin-right: 10px"  type="button" class=" mb-4 border  font-medium rounded-lg text-sm px-2 py-2 text-center me-2 mb-2 ">Skip >></a>
      <br/>
      <br/>
    @endif
    <div class="mt-5">
            <div class=" sm:rounded-lg">

            </div>
          </div>
          <div class="mt-5 mb-5"></div>

        <div class="">



          @if(auth()->user()->hasRole('admin'))
          <form action="" id="form-admin-dashboard" class="float-right" method="post">
            @csrf
            @method('POST')
            <input type="text" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="dates" name="dates" value="" >
            <button type="submit" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Filter</button>
          </form>

        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold mb-1">Dashboard</h1>

          <div class="mt-5">

          <div class="mt-5">
            <div class=" sm:rounded-lg">
              <ul role="list" class="grid grid-cols-4 gap-2">


              <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="flex w-full items-center justify-between space-x-6 p-6">
                  <div class="flex-1 truncate">
                    <div class="  ">
                      <h2 class="truncate  font-medium text-gray-900 text-3xl">
                        {{ $params['campaigns_remaining_in_queue'] }}
                      </h2>
                      <p>Campaigns inQueue</p>
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
      <ul role="list" class="grid grid-cols-1 gap-6">
        <li class="divide-y divide-gray-200 rounded-lg bg-white shadow">
          <div class="p-6 sm:rounded-lg">
            <h1 class="text-2xl text-slate-800 dark:text-slate-100 font-bold mb-1">Running Campaigns by User</h1>
              <table  class="mt-5 table-auto w-full ">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Campaigns</th>
                    <th class="px-4 py-2">Campaigns in Queue</th>
                    <th class="px-4 py-2">Latest Campaign CTR</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Tokens</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($accounts as $account)
                    <tr>
                      <td class="border border-gray-200 px-4 py-2"><a class="text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('accounts.show', $account->id) }}">{{ $account->name.($account->hasRole('admin')?'(administrator)':'') }}</a></td>
                      <td class="border border-gray-200 px-4 py-2">
                        <a class="text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400" href="/campaigns?user_id={{ $account->id }}">{{ $account->campaigns_count }}</a>
                      </td>
                      <td class="border border-gray-200 px-4 py-2">
                        <a class="text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400" href="/campaigns?user_id={{ $account->id }}&status=1">{{ $account->processing_campaign_count }}</td>
                      <td class="border border-gray-200 px-4 py-2">
                        {{ isset($account->latest_campaign_total_ctr) ? number_format($account->latest_campaign_total_ctr, 2) : 'No campaigns' }}
                      </td>
                      <td class="border border-gray-200 px-4 py-2">{{ $account->email }}</td>
                      <td class="border border-gray-200 px-4 py-2">{{ Number::format($account->tokens) }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="border border-gray-200 px-4 py-2 text-center">{{ __('No accounts found') }}</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
          </div>
        </li>
      </ul>
      @endif

    </div>
    </div>
</x-app-layout>
@push('scripts')
@if(auth()->user()->hasRole('admin'))
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
        // const startDate = new Date('2024-06-01');
        // const endDate = new Date('2024-06-02');

        // Generate dates for the X-axis
        // const labels = generateDates(startDate, endDate);
  // const labelsFormatted = labels.map(date => date.toISOString().split('T')[0]);

        // const labels = ['a', 'b', 'c', 'd'];

        // Convert date objects to ISO string format for labels
         const labelsFormatted = labels = {!! json_encode($labels) !!};

        // Dummy data for the four lines
        const dataLine1 = {!! json_encode($campaigns_graph) !!};
        const dataLine2 = {!! json_encode($send_graph) !!};
        const dataLine3 = {!! json_encode($clicks_graph) !!};
        const dataLine4 = {!! json_encode($ctr) !!};

        // Plotly data for the four lines
        const trace1 = {
            x: labelsFormatted,
            y: dataLine1,
            mode: 'lines',
            name: 'Campaigns',
            line: {color: 'rgba(255, 99, 132, 1)'}
        };
        const trace2 = {
            x: labelsFormatted,
            y: dataLine2,
            mode: 'lines',
            name: 'Send Messages',
            line: {color: 'rgba(54, 162, 235, 1)'}
        };
        const trace3 = {
            x: labelsFormatted,
            y: dataLine3,
            mode: 'lines',
            name: 'Clicks',
            line: {color: 'rgba(75, 192, 192, 1)'}
        };
        const trace4 = {
            x: labelsFormatted,
            y: dataLine4,
            mode: 'lines',
            name: 'CTR',
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

      $( document ).ready(function() {
          Plotly.newPlot('myLineChart', [trace1, trace2, trace3, trace4], layout);
      });

    </script>
@endif
