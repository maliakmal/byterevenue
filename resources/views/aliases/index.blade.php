<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto">

        <div class="mt-5">
            <div class=" sm:rounded-lg">

            </div>
        </div>
        <div class="mt-5 mb-5"></div>

        <div class="">

            <ul role="list" class="grid grid-cols-1 gap-6">
                <li class="divide-y divide-gray-200 rounded-lg bg-white shadow">
                    <div class="p-6 sm:rounded-lg">
                        <h1 class="text-2xl text-slate-800 dark:text-slate-100 font-bold mb-1">Aliases</h1>
                        <table  class="mt-5 table-auto w-full ">
                            <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2">ID</th>
                                <th class="px-4 py-2">Campaign id</th>
                                <th class="px-4 py-2">url</th>
                                <th class="px-4 py-2">flow id</th>
                                <th class="px-4 py-2">Response<br>(hover)</th>
                                <th class="px-4 py-2">created</th>
                                <th class="px-4 py-2">keitaro id</th>
                                <th class="px-4 py-2">keitaro response<br>(hover)</th>
                                <th class="px-4 py-2">url shortener id</th>
                                <th class="px-4 py-2">deleted</th>
                                <th class="px-4 py-2">error<br>(hover)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <style>
                                .hover-list:hover {
                                    background-color: rgba(242, 194, 0, 0.22);
                                }
                            </style>
                            @forelse ($aliases as $alias)
                                @if($alias->error)
                                    <tr class="hover-list" style="cursor: pointer" onclick="refreshRecords('{{ $alias->id }}')">
                                @else
                                    <tr style="cursor: default">
                                @endif
                                    <td class="border border-gray-200 px-4 py-2">{{ $alias->id }}</td>
                                    <td class="border border-gray-200 px-4 py-2"><a href="{{ route('campaigns.show', $alias->campaign_id) }}" style="color: blue">{{ $alias->campaign_id }}</a></td>
                                    <td class="border border-gray-200 px-4 py-2" style="overflow-wrap: anywhere">{{ $alias->url_shortener }}</td>
                                    <td class="border border-gray-200 px-4 py-2">{{ $alias->flow_id }}</td>
                                    <td class="border border-gray-200 px-4 py-2" title="{{ $alias->response }}">{{ $alias->response ? '🛈' : '---' }}</td>
                                    <td class="border border-gray-200 px-4 py-2">{{ $alias->created_at }}</td>
                                    <td class="border border-gray-200 px-4 py-2">{{ $alias->keitaro_campaign_id }}</td>
                                    <td class="border border-gray-200 px-4 py-2" title="{{ $alias->keitaro_campaign_response }}">{{ $alias->keitaro_campaign_response ? '🛈' : '---' }}</td>
                                    <td class="border border-gray-200 px-4 py-2">{{ $alias->url_shortener_id }}</td>
                                    <td class="border border-gray-200 px-4 py-2">{{ $alias->deleted_on_keitaro }}</td>
                                    <td class="border border-gray-200 px-4 py-2" title="{{ $alias->error }}">{{ $alias->error ? '🛈' : '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="border border-gray-200 px-4 py-2 text-center">{{ __('No files found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </li>
            </ul>

        </div>
    </div>

    <script>
        function refreshRecords(id) {
            if (confirm('Are you sure you want to refresh the records?'))
                window.location.href = '/aliases/' + id + '/refresh';
        }
    </script>
</x-app-layout>

