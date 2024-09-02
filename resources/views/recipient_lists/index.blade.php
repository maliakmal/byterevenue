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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Recipient Lists</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Recipient Lists</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

          <span class="ml-3 hidden sm:block">
            <a href="{{ route('recipient_lists.create') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
<svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
  <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
</svg>


              Add Recipient List
</a>
          </span>


        </div>
      </div>
    </header>


  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    @include('partials.alerts')
    <div class=" sm:rounded-lg">
    <div  class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div id="card-container" class="p-6 bg-white border-b border-gray-200">
            <form method="get" id="filter-form" action="{{route('recipient_lists.index')}}">
                <div class="flex flex-wrap -mx-3 mb-2" style="margin-bottom: 25px">

                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            Name
                        </label>
                        <input value="{{$_GET['name']??''}}" name="name" id="name_filter" type="text" style="height: 40px" class="border border-gray-300 text-gray-900 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                       dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                       dark:focus:border-blue-500" />
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            Is Imported
                        </label>
                        <select name="is_imported" id="is_imported_filter"  style="height: 40px" class="border border-gray-300 text-gray-900 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                       dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                       dark:focus:border-blue-500">
                            <option value="">Choose</option>
                            <option @if(($_GET['is_imported']??'') == '1') selected @endif  value="1">Yes</option>
                            <option @if(($_GET['is_imported']??'') == '0') selected @endif value="0">No</option>
                        </select>
                    </div>

                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0" style="padding: 10px" >
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
                    </div>
                </div>
            </form>


            <ul id="list-container" role="list" class="divide-y divide-gray-100">
                @foreach ($recipient_lists as $index=> $item)

                    <li class="flex justify-between gap-x-6 py-5">
                        <div class="flex min-w-0 gap-x-4">
                            <img class="h-12 w-12 flex-none bg-gray-50" src="/images/recipient.png" alt="">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm font-semibold leading-6 text-gray-900"> {{$item->name ?? "{NO NAME}"}}</p>
                                <p class="mt-1 truncate text-xs leading-5 text-gray-500">
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('accounts.show', $item->user_id) }}" class="flex text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 items-center">
                                            {{ $item->user?->name }}
                                    </a>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <div class="mt-1 flex items-center gap-x-1.5">
                                <div class="mt-1 flex items-center gap-x-1.5">
                                    <div class="flex-none rounded-full @if($item->is_imported == false) bg-yellow-100 @else bg-emerald-500/20 @endif p-1">
                                        <div class="h-1.5 w-1.5 rounded-full @if($item->is_imported == false) bg-yellow-500 @else  bg-emerald-500  @endif"></div>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-500">@if($item->is_imported == false ) Import In Progress @else Import Completed @endif</p>
                                </div>

                                <span class="text-xs">|</span>
                                <div class="mt-1 flex items-center gap-x-1.5">
                                    <a href="{{route('recipient_lists.edit', $item->id) }}" style="color: dodgerblue" class="text-xs leading-5 text-gray-500">EDIT</a>
                                </div>
                                <span class="text-xs">|</span>
                                <form method="post" action="{{route('recipient_lists.destroy', $item->id) }}">
                                    <div class="mt-1 flex items-center gap-x-1.5">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs leading-5 " style="color:darkred"  href="{{route('recipient_lists.destroy', $item->id) }}">DELETE</button>
                                    </div>
                                </form>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-gray-500"> {{$item->contacts_count}}  Num Recipients | {{$item->campaigns_count}} Campaigns Used | {{$item->source ?? "..." }} source</p>
                        </div>
                    </li>
                @endforeach
            </ul>

</div>
        <div style="margin-top: 20px">
            <button id="loadDataButton" style="margin: 0 auto" type="submit"  class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
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
        var page = 1;

        $(document).ready(function() {
            var myEl = document.getElementById('loadDataButton');
            myEl.addEventListener('click', function () {
                $.LoadingOverlay("show");
                // var area_code = $('#city-filter').val();
                // var phone = $('#phone').val();
                var name = $('#name_filter').val();
                var isImported = $('#is_imported_filter').val();
                page++;
                {{--var url = "{!! route('recipient_lists.index') !!}" + "?output=json&page=" + page + "&phone=" + phone + "&area_code=" + area_code + "&name=" + name;--}}
                var url = "{!! route('recipient_lists.index') !!}" + "?output=json&page=" + page + "&name=" + name+ "&is_imported=" + isImported ;
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


            function getElementString(ele){
                var edit = "{!!  route('recipient_lists.edit', 'id') !!}" ;
                var del = "{!!  route('recipient_lists.destroy', 'id') !!}" ;
                var account_link = "{!! route('accounts.show', 'user_id') !!}" ;
                edit = edit.replace('id', ele.id);
                del = del.replace('id', ele.id);
                account_link = account_link.replace('user_id', ele.user_id);


                return `
                <li class="flex justify-between gap-x-6 py-5">
                        <div class="flex min-w-0 gap-x-4">
                            <img class="h-12 w-12 flex-none bg-gray-50" src="/images/recipient.png" alt="">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm font-semibold leading-6 text-gray-900"> ${ele.name || "{NO NAME}"}</p>
                                <p class="mt-1 truncate text-xs leading-5 text-gray-500">

                <a href="${account_link}" class="flex text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 items-center">
                                            ${ ele?.user?.name || "" }
                </a>
                </p>
            </div>
        </div>
        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
            <div class="mt-1 flex items-center gap-x-1.5">
                <div class="mt-1 flex items-center gap-x-1.5">
                    <div class="flex-none rounded-full ${ele.is_imported == false ? "bg-yellow-100" : "bg-emerald-500/20"} p-1">
                                        <div class="h-1.5 w-1.5 rounded-full ${ele.is_imported == false ? "bg-yellow-500" :  "bg-emerald-500"}" ></div>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-500">${ele.is_imported == false ? "Import In Progress" : "Import Completed"}</p>
                                </div>

                                <span class="text-xs">|</span>
                                <div class="mt-1 flex items-center gap-x-1.5">
                                    <a href="${edit}" style="color: dodgerblue" class="text-xs leading-5 text-gray-500">EDIT</a>
                                </div>
                                <span class="text-xs">|</span>
                                <form method="post" action="${del}">
                                    <div class="mt-1 flex items-center gap-x-1.5">
                                        @csrf
                @method('DELETE')
                <button class="text-xs leading-5 " style="color:darkred">DELETE</button>
                                    </div>
                                </form>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-gray-500"> ${ele.contacts_count}  Num Recipients | ${ele.campaigns_count} Campaigns Used | ${ele.source || "..." } source</p>
                        </div>
                    </li>`;
            }

        });
    </script>
