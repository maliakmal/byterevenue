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
                                <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">BlackListNumber</a>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">User Black List Numbers</h1>
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
                </div>
            </div>
            <div class="mt-5 flex xl:mt-0 xl:ml-4">
            </div>
        </div>
    </header>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.alerts')
            <div class=" sm:rounded-lg">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        @if(count($list)>0)
                            <div>
                                <form method="get" id="filter-form">
                                    <select id="count" name="count">
                                        <option value="">Count</option>
                                        <option  {{ $filter['count']=='5'?'selected' :'' }}  value="5">5</option>
                                        <option  {{ $filter['count']=='10'?'selected' :'' }}  value="10">10</option>
                                        <option  {{ $filter['count']=='50'?'selected' :'' }}  value="50">50</option>
                                        <option  {{ $filter['count']=='100'?'selected' :'' }}  value="100">100</option>
                                    </select>
                                </form>
                            </div>

                            <table  class="mt-5 table-auto w-full">
                                <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3">Phone Number</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($list as $index=>$item)

                                    <tr>
                                        <td style="text-align: center"  class="border-b border-gray-200 px-4 py-2">{{ $item->phone }}</td>

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
                                        <h3 class="truncate text-sm font-medium text-gray-900">No Black List Number found</h3>

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


    </script>
