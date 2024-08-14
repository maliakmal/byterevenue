<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto">

        <!-- Welcome banner -->
        <x-dashboard.welcome-banner />

        <!-- Dashboard actions -->
                @if(auth()->user()->hasRole('admin') == false && auth()->user()->show_introductory_screen == true)

            <section class="bg-gray-50 py-12">
                <a href="/introductory/disable" style="float: right;margin-right: 10px"  type="button" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-2 py-2 text-center me-2 mb-2 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900">Close</a>

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <p class="text-sm font-bold uppercase tracking-widest text-gray-700">How It Works</p>
                        <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl lg:text-5xl">Welcome to Byte Revenue
                        </h2>
                        <p class="mx-auto mt-4 max-w-2xl text-lg font-normal text-gray-700 lg:text-xl lg:leading-8">
                            Lets get you started

                        </p>
                    </div>



<div style="margin-top: 30px" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">
                    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
                            <div>
                                <div>
                                    <svg style="margin:  0 auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"
                                         class="h-12 w-25 text-gray-600 group-hover:text-white">
                                        <path
                                            d="M21 12C21 13.6569 16.9706 15 12 15C7.02944 15 3 13.6569 3 12M21 5C21 6.65685 16.9706 8 12 8C7.02944 8 3 6.65685 3 5M21 5C21 3.34315 16.9706 2 12 2C7.02944 2 3 3.34315 3 5M21 5V19C21 20.6569 16.9706 22 12 22C7.02944 22 3 20.6569 3 19V5"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>
                                <div class="ml-6 lg:ml-0 lg:mt-10">
                                    <h3
                                        class="text-xl font-bold text-gray-900 before:mb-2 before:block before:font-mono before:text-sm before:text-gray-500">
                                        Import Reception List
                                    </h3>
                                    <h4 class="mt-2 text-base text-gray-700">Import a Recipient List to build a list of contacts</h4>
                                </div>
                            </div>
                        <div style="margin-top: 20px">
                            <a href="/recipient_lists/create" class="w-full text-white @if(!$has_reception_list) bg-blue-700 @else  bg-gray-500 @endif hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Reception List</a>
                        </div>
                    </div>
    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
        <div>
            <div>
                <svg style="margin: 0 auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"
                     class="h-12 w-25 text-gray-600 group-hover:text-white">
                    <path
                        d="M5.50049 10.5L2.00049 7.9999L3.07849 6.92193C3.964 6.03644 4.40676 5.5937 4.9307 5.31387C5.39454 5.06614 5.90267 4.91229 6.42603 4.86114C7.01719 4.80336 7.63117 4.92617 8.85913 5.17177L10.5 5.49997M18.4999 13.5L18.8284 15.1408C19.0742 16.3689 19.1971 16.983 19.1394 17.5743C19.0883 18.0977 18.9344 18.6059 18.6867 19.0699C18.4068 19.5939 17.964 20.0367 17.0783 20.9224L16.0007 22L13.5007 18.5M7 16.9998L8.99985 15M17.0024 8.99951C17.0024 10.1041 16.107 10.9995 15.0024 10.9995C13.8979 10.9995 13.0024 10.1041 13.0024 8.99951C13.0024 7.89494 13.8979 6.99951 15.0024 6.99951C16.107 6.99951 17.0024 7.89494 17.0024 8.99951ZM17.1991 2H16.6503C15.6718 2 15.1826 2 14.7223 2.11053C14.3141 2.20853 13.9239 2.37016 13.566 2.5895C13.1623 2.83689 12.8164 3.18282 12.1246 3.87469L6.99969 9C5.90927 10.0905 5.36406 10.6358 5.07261 11.2239C4.5181 12.343 4.51812 13.6569 5.07268 14.776C5.36415 15.3642 5.90938 15.9094 6.99984 16.9998V16.9998C8.09038 18.0904 8.63565 18.6357 9.22386 18.9271C10.343 19.4817 11.6569 19.4817 12.7761 18.9271C13.3643 18.6356 13.9095 18.0903 15 16.9997L20.1248 11.8745C20.8165 11.1827 21.1624 10.8368 21.4098 10.4331C21.6291 10.0753 21.7907 9.6851 21.8886 9.27697C21.9991 8.81664 21.9991 8.32749 21.9991 7.34918V6.8C21.9991 5.11984 21.9991 4.27976 21.6722 3.63803C21.3845 3.07354 20.9256 2.6146 20.3611 2.32698C19.7194 2 18.8793 2 17.1991 2Z"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div class="ml-6 lg:ml-0 lg:mt-10">
                <h3
                    class="text-xl font-bold text-gray-900 before:mb-2 before:block before:font-mono before:text-sm before:text-gray-500">
                    Create Campaign
                </h3>
                <h4 class="mt-2 text-base text-gray-700"> create your first campaign here for and process</h4>
            </div>
        </div>
        <div style="margin-top: 20px">
            <a href="/campaigns/create" class="w-full text-white @if(!$has_campaign) bg-blue-700 @else  bg-gray-500 @endif  hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">create campaign</a>
        </div>
    </div>

</div>

                </div>
            </section>


            <br/>
<br/>
<br/>
<br/>
        @endif

        <div class="">
          <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold mb-1">Campaigns to Watch</h1>
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
                        </tbody>
                  </table>
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
    </div>
</x-app-layout>
