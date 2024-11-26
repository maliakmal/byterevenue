<x-app-layout>
<header class="bg-gray-50 py-8">

    <main class="py-6 px-12 space-y-12 bg-gray-100 min-h-screen w-full">
        <div class="flex flex-col h-full w-full mx-auto  space-y-6">
            <section class="flex flex-col mx-auto bg-white rounded-lg p-6 shadow-md space-y-6 w-full">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full min-w-0">
                    <!-- In use -->
                    <div class="flex flex-col px-6 py-2 bg-white shadow rounded-lg overflow-hidden">
                        <div class="flex flex-col items-center space-y-2">
                            <div style="font-size: 30px;margin-top: 15px" class=" font-bold tracking-tight leading-none text-black-500"> {{ substr($campaign->title, 0 ,16) }} @if(strlen($campaign->title) > 18) ... @endif</div>
                            <div style="margin-top: 15px" class="text-lg font-medium text-black-500">Campaign</div>
                        </div>
                    </div>
                    <!-- renovation -->
                    <div class="flex flex-col px-6 py-2 bg-white shadow rounded-lg overflow-hidden">
                        <div class="flex flex-col items-center space-y-2">
                            <div style="font-size: 30px;margin-top: 15px" class="text-center font-bold tracking-tight leading-none  text-black-500">{{ $campaign?->recipient_list?->name }}</div>
                            <div style="margin-top: 15px" class="text-center  text-lg font-medium  text-black-500">Recipient List</div>
                        </div>
                    </div>
                    <!-- Suspended -->
                    <div class="flex flex-col px-6 py-2 bg-white shadow rounded-lg overflow-hidden">
                        <div class="flex flex-col items-center space-y-2">
                            <div style="font-size: 30px;margin-top: 15px" class="text-center text-6xl font-bold tracking-tight leading-none">{{ $campaign?->user?->tokens}}</div>
                            <div style="margin-top: 15px" class="text-lg font-medium">Available Tokens</div>
                        </div>
                    </div>
                    <!-- Closed -->
                    <div class="flex flex-col px-6 py-2 bg-white shadow rounded-lg overflow-hidden">
                            @switch($campaign->status)
                       @case(\App\Models\Campaign::STATUS_DRAFT)
                         <div style="font-size: 30px;margin-top: 15px"  class=" text-center text-6xl font-bold tracking-tight leading-none text-blue-900">Draft</div>
                         @break
                          @case(\App\Models\Campaign::STATUS_PROCESSING)
                            <div style="font-size: 30px;margin-top: 15px"  class="text-center text-6xl font-bold tracking-tight leading-none text-yellow-800">Processing</div>
                          @break
                      @case(\App\Models\Campaign::STATUS_DONE)
                        <div style="font-size: 30px;margin-top: 15px"  class="text-center text-6xl font-bold tracking-tight leading-none text-green-900">Done</div>
                        @break
                  @endswitch
                            <div style="margin-top: 15px" class="text-center text-lg font-medium text-primary-900">Status</div>
                   </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full min-w-0">
                    <div>

                        @if($campaign->canBeDeleted())


                            <div class="inline-flex rounded-md shadow-sm" role="group">
                                <a href="{{ route('campaigns.markProcessed', ['id'=>$campaign->id]) }}"  type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                    Process
                                </a>

                                <a href="{{ route('campaigns.edit', $campaign->id) }}?campaign_id={{ $campaign->id }}" type="button" class="focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:focus:ring-yellow-900">
                                    Edit
                                </a>

                                <form action="{{ route('campaigns.destroy', $campaign->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
                                        Delete
                                    </button>
                                </form>


                            </div>

                        @endif

                    </div>
                    <div> </div>
                    <div> </div>
                    <div>
                        @if(auth()->user()->hasRole('admin'))
                            <p class="text-gray-700"><b>Unique Folder:</b> {{ $campaign->getUniqueFolder() }}</p>
                    </div>
                    </div>

            @endif
            </section>
            <section class="flex flex-col mx-auto bg-white rounded-lg p-6 shadow-md space-y-6 w-full">
                @if($campaign->isDispatched())
                    <h1 class="text-2xl">
                        <b>Messages</b>
                    </h1>
                @else
                    <h1 class="text-2xl"> <b>Contacts</b> </h1>
                @endif
                <div style="margin-top: 0px" class="py-12">

                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        @include('partials.alerts')
                        <ul id="list-message-container" role="list" class="divide-y divide-gray-100">
                                @if($campaign->isDispatched())
                                @foreach ($logs  as $log)
                                <li class="flex justify-between gap-x-6 py-5">
                                    <div class="flex min-w-0 gap-x-4">
                                        <img style="border-radius: 50%" class="h-12 w-12 flex-none bg-gray-50" src="/images/msg.png" alt="">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold leading-6 text-gray-900"> {{ $log->recipient_phone }}</p>
                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                                                {{ $log->message_body }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                        <div class="mt-1 flex items-center gap-x-1.5">
                                            <div class="mt-1 flex items-center gap-x-1.5">
                                                <div class="flex-none rounded-full bg-yellow-100  p-1">
                                                    <div class="h-1.5 w-1.5 rounded-full bg-yellow-500"></div>
                                                </div>
                                                <p class="text-xs leading-5 text-gray-500">UNDER PROCESS </p>
                                            </div>

                                        </div>
                                        <p class="mt-1 text-xs leading-5 text-gray-500">
                                            {{ $log->created_at }}
                                        </p>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                                    </div>

                        <button id="loadLogDataButton" style="margin: 0 auto" type="submit"  class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                            Load More ...
                        </button>
                                @else
                        <ul id="list-container-contact" role="list" class="divide-y divide-gray-100">
                            @foreach($contacts as $contact)
                                <li class="flex justify-between gap-x-6 py-5">
                                    <div class="flex min-w-0 gap-x-4">
                                        <img style="border-radius: 50%" class="h-12 w-12 flex-none bg-gray-50" src="/images/usr.png" alt="">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold leading-6 text-gray-900"> {{ $contact->phone }}</p>
                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                                                 {{ $message?->getParsedMessage() }}

                                            </p>
                                        </div>
                                    </div>
                                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                        <div class="mt-1 flex items-center gap-x-1.5">
                                        </div>
                                        <p class="mt-1 text-xs leading-5 text-gray-500">
                                            Name: {{ $contact->name }} | Email: {{ $contact->email }}

                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                            <button id="loadContactDataButton" style="margin: 0 auto" type="submit"  class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                Load More ...
                            </button>
                                @endif
                </div>
            </section>
        </div>
    </main>
    </header>
</x-app-layout>
@push('scripts')
    <script>
        var contactPage = 1;
        var logPage = 1;

        $(document).ready(function() {

            var myEl2 = document.getElementById('loadContactDataButton');
            if(myEl2) {
                myEl2.addEventListener('click', function () {
                    $.LoadingOverlay("show");
                    contactPage++;
                    var url = "{!! route('campaigns.show', $campaign->id ) !!}" + "?page=" + contactPage + "&output=json";
                    $.get(url, function (data) {
                        if (data.data.contacts.last_page <= contactPage) {
                            $('#loadContactDataButton').remove();
                        }
                        var elements = data.data.contacts.data;
                        var str = "";
                        var i = 0;
                        for (i = 0; i < elements.length; i++) {
                            str += getContactElementString(elements[i], "{{$message?->getParsedMessage()}}");
                        }
                        $('#list-container-contact').append(str);
                        $.LoadingOverlay("hide");

                    });

                });
            }

            function getContactElementString(ele, parsedMessage){
                return `
                 <li class="flex justify-between gap-x-6 py-5">
                                    <div class="flex min-w-0 gap-x-4">
                                        <img style="border-radius: 50%" class="h-12 w-12 flex-none bg-gray-50" src="/images/usr.png" alt="">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold leading-6 text-gray-900"> ${ele.phone}</p>
                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                                                 ${parsedMessage}

                </p>
            </div>
        </div>
        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
            <div class="mt-1 flex items-center gap-x-1.5">
            </div>
            <p class="mt-1 text-xs leading-5 text-gray-500">
                Name: ${ele.name} | Email: ${ele.email}

                </p>
            </div>
        </li>
`;
            }




            var myEl = document.getElementById('loadLogDataButton');
            if(myEl) {
                myEl.addEventListener('click', function () {
                    $.LoadingOverlay("show");
                    logPage++;
                    var url = "{!! route('campaigns.show', $campaign->id ) !!}" + "?page=" + logPage + "&output=json";
                    $.get(url, function (data) {
                        if (data.data.logs.last_page <= logPage) {
                            $('#loadLogDataButton').remove();
                        }
                        var elements = data.data.logs.data;
                        var str = "";
                        var i = 0;
                        for (i = 0; i < elements.length; i++) {
                            str += getLogElementString(elements[i]);
                        }
                        $('#list-message-container').append(str);
                        $.LoadingOverlay("hide");

                    });

                });
            }


            function getLogElementString(ele){
                return `
                 <li class="flex justify-between gap-x-6 py-5">
                                    <div class="flex min-w-0 gap-x-4">
                                        <img style="border-radius: 50%" class="h-12 w-12 flex-none bg-gray-50" src="/images/msg.png" alt="">
                                        <div class="min-w-0 flex-auto">
                                            <p class="text-sm font-semibold leading-6 text-gray-900"> ${ ele.recipient_phone }</p>
                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                                                ${ ele.message_body }
                </p>
            </div>
        </div>
        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
            <div class="mt-1 flex items-center gap-x-1.5">
                <div class="mt-1 flex items-center gap-x-1.5">
                    <div class="flex-none rounded-full bg-yellow-100  p-1">
                        <div class="h-1.5 w-1.5 rounded-full bg-yellow-500"></div>
                    </div>
                    <p class="text-xs leading-5 text-gray-500">UNDER PROCESS </p>
                </div>

            </div>
            <p class="mt-1 text-xs leading-5 text-gray-500">
    ${ ele.created_at }
                </p>
            </div>
        </li>
`;
            }
        });

    </script>
