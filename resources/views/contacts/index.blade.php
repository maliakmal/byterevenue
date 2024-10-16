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
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-400"
                                     x-description="Heroicon name: mini/chevron-right"
                                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                     aria-hidden="true">
                                    <path fill-rule="evenodd"
                                          d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                          clip-rule="evenodd"></path>
                                </svg>
                                <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Data-Source</a>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Data-Source</h1>
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
                </div>
            </div>
            <div class="mt-5 flex xl:mt-0 xl:ml-4">

          <span class="ml-3 hidden sm:block">
            <a href="{{ route('data-source.create') }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
<svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
     fill="currentColor">
  <path fill-rule="evenodd"
        d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z"
        clip-rule="evenodd"/>
</svg>


              New Contact
</a>
          </span>


            </div>
        </div>
    </header>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('partials.alerts')
    <div class=" sm:rounded-lg">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div id="card-container" class="p-6 bg-white">
            <form method="get" id="filter-form" action="{{route('data-source.index')}}">
                <div class="flex flex-wrap -mx-3 mb-2" style="margin-bottom: 25px">
                    <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
                            Province
                        </label>
                        <div class="relative">
                            <select name="province" style="width: 100%" id="province-filter">
                                <option value="">Select a province ...</option>
                                @foreach($area_data['provinces'] as $area)
                                    <option value="{{$area->province}}">{{$area->province}}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                    <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            City
                        </label>
                        <select name="area_code" class="bg-gray-50  text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" style="width: 100%" id="city-filter">
                            <option value="">Select a city ...</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            Phone
                        </label>
                       <input value="{{$_GET['phone']??''}}" name="phone" id="phone" type="number" style="height: 30px" class="border border-gray-300 text-gray-900 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                       dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                       dark:focus:border-blue-500" />
                    </div>
                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            Name
                        </label>
                        <input value="{{$_GET['name']??''}}" name="name" id="name"  style="height: 30px" class="border border-gray-300 text-gray-900 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                       dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                       dark:focus:border-blue-500" />
                    </div>

                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0" style="padding: 10px" >
                        <button type="submit" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Submit</button>
                    </div>
                </div>
            </form>


            <ul role="list" class="divide-y divide-gray-100">
                @foreach ($contacts as $index => $contact)

                <li class="flex justify-between gap-x-6 py-5">
                    <div class="flex min-w-0 gap-x-4">
                        <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="/images/Sample_User_Icon.png" alt="">
                        <div class="min-w-0 flex-auto">

                            <p class="text-sm font-semibold leading-6 text-gray-900"> {{$contact->name ?? "{".$contact->phone."}"}}</p>
                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">{{$contact->phone}}  |  {{$contact->email}}</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <div class="mt-1 flex items-center gap-x-1.5">
                            <div class="mt-1 flex items-center gap-x-1.5">
                                <div class="flex-none rounded-full @if($contact->black_list_number_count >= 1) bg-red-200 @else bg-emerald-500/20 @endif p-1">
                                    <div class="h-1.5 w-1.5 rounded-full @if($contact->black_list_number_count >= 1) bg-red-500 @else  bg-emerald-500  @endif"></div>
                                </div>
                                <p class="text-xs leading-5 text-gray-500">@if($contact->black_list_number_count >=1 ) Blocked @else Available @endif</p>
                            </div>
                            <span class="text-xs">|</span>
                            <div class="mt-1 flex items-center gap-x-1.5">
                                <a href="{{route('data-source.edit', $contact->id) }}" style="color: dodgerblue" class="text-xs leading-5 text-gray-500">EDIT</a>
                            </div>
                            <span class="text-xs">|</span>
                                <form method="post" action="{{route('data-source.destroy', $contact->id) }}">
                                    <div class="mt-1 flex items-center gap-x-1.5">

                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs leading-5 " style="color:darkred"  href="{{route('data-source.destroy', $contact->id) }}">DELETE</button>
                                    </div>

                                </form>

                        </div>

                        <p class="mt-1 text-xs leading-5 text-gray-500"> {{$contact->sent_messages_count}}  sent  message | {{$contact->recipient_lists_count}} recipients | {{$contact->campaigns_count}} campaigns</p>


                    </div>
                </li>
                @endforeach
            </ul>
        </div>
          <div style="clear: both"></div>
          <div style="margin-top: 20px">
              <button id="loadDataButton" style="margin: 0 auto" type="submit"  class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                  Load More ...
              </button>
          </div>
      </div>
    </div>
  </div>

</x-app-layout>

@push('scripts')
<script>
    var cities = [];
    var page = 1;

    $(document).ready(function() {
        $('#province-filter').select2({
            width: '100%',
            height: '500px',
        });
        $('#city-filter').select2({
            width: '100%',
            height: '500px',
        });

        var province_filter = '{{$_GET['province'] ?? ''}}';
        var city_filter = '{{$_GET['area_code'] ?? ''}}';
        if (province_filter){
            $('#province-filter').val(province_filter).trigger('change');
            loadCitites(province_filter);
            if(area_code_filter){
                $('#city-filter').val(area_code_filter).trigger('change');

            }
        }
        var area_code_filter = '{{$_GET['area_code'] ?? ''}}';
        if (area_code_filter){
            $('#area_code-filter').val(area_code_filter).trigger('change');
        }

        var myEl = document.getElementById('loadDataButton');
        myEl.addEventListener('click', function() {
            $.LoadingOverlay("show");
            var area_code = $('#city-filter').val();
            var phone = $('#phone').val();
            var name = $('#name').val();
            page++;
            var url = "{!! route('data-source.index') !!}" +"?output=json&page="+page+"&phone="+phone+"&area_code="+area_code+"&name="+name;
            $.get(url, function(data){
                if (data.data.last_page <= page){
                    $('#loadDataButton').remove();
                }
                var elements = data.data.data;
                var str = "";
                var i = 0;
                for (i=0; i < elements.length; i++){
                    str += getElementString(elements[i]);
                }
                $('#card-container').append(str);
                $.LoadingOverlay("hide");
            });

        });

        $('#province-filter').on('select2:select', function (e) {
            var province = $('#province-filter').val();
            loadCitites(province);

        });

        function loadCitites(province){
            $('#city-filter').html("");
            $.LoadingOverlay("show");
            $.get('api/areas/cities-by-province/' + province, function(data){
                var i;
                for (i=0; i < data.length; i++) {
                    var city = {
                        id: data[i].code,
                        text: data[i].city_name+" (+"+data[i].code+")"
                    };
                    var newOption = new Option(city.text, city.id, false, false);
                    $('#city-filter').append(newOption);
                }
            });
            $('#city-filter').trigger('change');
            $.LoadingOverlay("hide");
        }

        function getElementString(ele){
            var edit = "{!!  route('data-source.edit', 'id') !!}" ;
            var del = "{!!  route('data-source.destroy', 'id') !!}" ;
            edit = edit.replace('id', ele.id);
            del = del.replace('id', ele.id);

            return `
        <li class="flex justify-between gap-x-6 py-5">
                <div class="flex min-w-0 gap-x-4">
                    <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="/images/Sample_User_Icon.png" alt="">
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm font-semibold leading-6 text-gray-900"> ${ele.name || "{"+ele.phone+"}"}</p>
                        <p class="mt-1 truncate text-xs leading-5 text-gray-500">${ele.phone +" | "+ ele.email}</p>
                    </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">


            <div class="mt-1 flex items-center gap-x-1.5">
                        <div class="mt-1 flex items-center gap-x-1.5">
                            <div class="flex-none rounded-full ${ele.black_list_number_count >=1 ? "bg-red-200" : "bg-emerald-500/20" } p-1">
                                <div class="h-1.5 w-1.5 rounded-full ${ele.black_list_number_count >=1 ? "bg-red-500" : "bg-emerald-500" }"></div>
                            </div>
                            <p class="text-xs leading-5 text-gray-500">${ele.black_list_number_count >=1  ? "Blocked" : "Available" }</p>
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

                        <p class="mt-1 text-xs leading-5 text-gray-500">${ele.sent_messages_count} sent  message | ${ele.recipient_lists_count} recipients | ${ele.campaigns_count} campaigns</p>

                </div>
            </li>`;
        }
    });

</script>
