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
                                    x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <a href="#"
                                    class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Accounts</a>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Accounts</h1>
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
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div>
                        <form method="get" id="filter-form">
                            <select id="sortby" name="sortby">
                                <option value="">Sort By</option>
                                <option {{ $filter['sortby']=='id_desc' ?'selected' :'' }} value="id_desc">Latest to
                                    Oldest</option>
                                <option {{ $filter['sortby']=='id_asc' ?'selected' :'' }} value="id_asc">Oldest to
                                    Latest</option>
                                <option {{ $filter['sortby']=='tokens_desc' ?'selected' :'' }} value="tokens_desc">
                                    Tokens(High to Less)</option>
                                <option {{ $filter['sortby']=='tokens_asc' ?'selected' :'' }} value="tokens_asc">
                                    Tokens(Less to High)</option>
                                <option {{ $filter['sortby']=='name' ?'selected' :'' }} value="name">Name -
                                    Alphabetically</option>
                            </select>
                            <select id="count" name="count">
                                <option value="">Count</option>
                                <option {{ $filter['count']=='5' ?'selected' :'' }} value="5">5</option>
                                <option {{ $filter['count']=='10' ?'selected' :'' }} value="10">10</option>
                                <option {{ $filter['count']=='50' ?'selected' :'' }} value="50">50</option>
                                <option {{ $filter['count']=='100' ?'selected' :'' }} value="100">100</option>
                            </select>
                        </form>
                    </div>
                    <table class="mt-5 table-auto w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Campaigns</th>
                                <th class="px-4 py-2">Latest Campaign CTR</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Tokens</th>
                                <th class="px-4 py-2">Roles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accounts as $account)
                            <tr>
                                <td class="border border-gray-200 px-4 py-2">
                                    <a class="text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('accounts.show', $account->id) }}">
                                        {{ $account->name.($account->hasRole('admin')?'(administrator)':'') }}
                                    </a>
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    {{ $account->campaigns_count }}
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    {{ isset($account->latest_campaign_total_ctr) ? number_format($account->latest_campaign_total_ctr, 2) : 'No campaigns' }}
                                </td>
                                <td class="border border-gray-200 px-4 py-2">{{ $account->email }}</td>
                                <td class="border border-gray-200 px-4 py-2">{{ Number::format($account->tokens) }}</td>
                                <td>
                                <form id="role-form" action="{{ route('accounts.assignRole', $account->id) }}" method="POST">
                                    @csrf
                                    <select name="role">
                                        <option >Select Role</option>
                                    @foreach($roles as $role)
                                        <option {{ $account->hasRole($role->name) ? 'selected' : '' }} value="{{ $role->name }}" >{{ $role->name }}</option>
                                    @endforeach
                                    </select>
                                </form>

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="border border-gray-200 px-4 py-2 text-center">{{ __('No accounts found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <br />
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@push('scripts')
<script>

    document.addEventListener('DOMContentLoaded', function () {
        var selectElements = document.querySelectorAll('#role-form select');
        var form = document.getElementById('role-form');

        selectElements.forEach(function (selectElement) {
            selectElement.addEventListener('change', function () {
                if (form) {
                    form.submit();
                }
            });
        });
    });


</script>
