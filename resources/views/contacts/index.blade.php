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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Data-Source</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Data-Source</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

          <span class="ml-3 hidden sm:block">
            <a href="{{ route('data-source.create') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
<svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
  <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
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
        <div id="card-container" class="p-6 bg-white border-b border-gray-200">
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
                    <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            Phone
                        </label>
                       <input value="{{$_GET['phone']??''}}" name="phone" id="phone" type="number" style="height: 30px" class="border border-gray-300 text-gray-900 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                       dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                       dark:focus:border-blue-500" />
                    </div>
                    <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">
                            Name
                        </label>
                        <input value="{{$_GET['name']??''}}" name="name" id="name"  style="height: 30px" class="border border-gray-300 text-gray-900 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                       dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                       dark:focus:border-blue-500" />
                    </div>
                    <div style="padding: 10px" class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
                    </div>
                </div>
            </form>

        @forelse ($contacts as $index=> $contact)
                <div style="height:300px;width: 30%;float: left" class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex justify-end px-4 pt-4">
                        <button>

                        </button>
                    </div>
                    <div class="flex flex-col items-center pb-10">
                        <img class="w-24 h-24 mb-3 rounded-full shadow-lg" src="/images/Sample_User_Icon.png" alt="Bonnie image"/>
                        <h5 class="mb-1 text-xl font-medium text-gray-900 dark:text-white">{{ $contact->name }}</h5>
                        <span style="margin-top: 5px" class="text-sm text-gray-500 dark:text-gray-400">
                            <b>Email: </b>   {{ $contact->email }}
                        </span>
                        <span style="margin-top: 5px" class="text-sm text-gray-500 dark:text-gray-400">
                            <b>Phone: </b>
                            {{ $contact->phone }}
                        </span>
                        <div class="flex mt-4 md:mt-6">
                            <a href="{{ route('data-source.edit', $contact->id) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-green-800">
                                 Details
                            </a>
                            <form action="{{ route('data-source.destroy', $contact->id) }}" method="post" class="relative -ml-px inline-flex flex-1 items-center justify-center">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="py-2 px-4 ms-2 text-sm font-medium text-red-900 focus:outline-none bg-white rounded-lg border border-red-200 hover:bg-red-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-700 dark:bg-red-800 dark:text-red-400 dark:border-red-600 dark:hover:text-white dark:hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
                @endforeach

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
    var cities = {!! $area_data['cities'] !!};
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
        if(province_filter){
            $('#province-filter').val(province_filter).trigger('change');
            loadCitites(province_filter);
            if(area_code_filter){
                $('#city-filter').val(area_code_filter).trigger('change');

            }
        }
        var area_code_filter = '{{$_GET['area_code'] ?? ''}}';
        if(area_code_filter){
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
                    if(data.data.last_page <= page){
                        $('#loadDataButton').remove();
                    }
                    var elements = data.data.data;
                    var str = "";
                    var i = 0;
                    for(i=0;i<elements.length;i++){
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
            var i;
            $('#city-filter').html("");
            $.LoadingOverlay("show");
            for(i=0; i<cities.length;i++){
                if(cities[i].province == province){
                    var data = {
                        id: cities[i].code,
                        text: cities[i].city_name+" (+"+cities[i].code+")"
                    };
                    var newOption = new Option(data.text, data.id, false, false);
                    $('#city-filter').append(newOption);
                }
            }
            $('#city-filter').trigger('change');
            $.LoadingOverlay("hide");
        }

        function getElementString(ele){
            var edit = "{!!  route('data-source.edit', 'id') !!}" ;
            var del = "{!!  route('data-source.destroy', 'id') !!}" ;
            edit = edit.replace('id', ele.id);
            del = del.replace('id', ele.id);
            return `<div style="height:300px;width: 30%;float: left" class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-end px-4 pt-4">
                <button id="dropdownButton${ele.id}" data-dropdown-toggle="dropdown${ele.id}" class="inline-block text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-1.5" type="button">
                <span class="sr-only">Open dropdown</span>

        </button>
            <!-- Dropdown menu -->
            <div id="dropdown${ele.id}" class="z-10 hidden text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                <ul class="py-2" aria-labelledby="dropdownButton">
                    <li>
                        <a href="${edit}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Edit</a>
                    </li>
                    <li>
                        <form action="${del}" method="post" class="relative -ml-px inline-flex flex-1 items-center justify-center">
                            @csrf
                            @method('DELETE')
                            <button type="submit"  class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                            Delete
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
            <div class="flex flex-col items-center pb-10">
                <img class="w-24 h-24 mb-3 rounded-full shadow-lg" src="/images/Sample_User_Icon.png" alt="Bonnie image"/>
                <h5 class="mb-1 text-xl font-medium text-gray-900 dark:text-white">${ele.name}</h5>
                <span style="margin-top: 5px" class="text-sm text-gray-500 dark:text-gray-400">
                            <b>Email: </b>  ${ele.email}
            </span>
            <span style="margin-top: 5px" class="text-sm text-gray-500 dark:text-gray-400">
                <b>Phone: </b>
${ele.phone}
            </span>
    <div class="flex mt-4 md:mt-6">
        <a href="${edit}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-green-800">
                        Details
                    </a>


                    <form action="${del}" method="post" class="relative -ml-px inline-flex flex-1 items-center justify-center">
                                @csrf
            @method('DELETE')
            <button type="submit" class="py-2 px-4 ms-2 text-sm font-medium text-red-900 focus:outline-none bg-white rounded-lg border border-red-200 hover:bg-red-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-700 dark:bg-red-800 dark:text-red-400 dark:border-red-600 dark:hover:text-white dark:hover:bg-red-700">
                Delete
            </button>
        </form>


</div>

</div>
</div>`;
        }
    });

</script>
