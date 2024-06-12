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
                  <a href="/accounts" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Accounts</a>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
                  </svg>
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $account->name }}</a> 
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">{{ $account->name }} 
                  </h1> 
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
            {{ $account->email }}
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">

        <div class="flex bg-white overflow-hidden shadow-xl sm:rounded-lg w-full items-center justify-between space-x-6 p-6">
          <div class="flex-1 truncate">
            <div class="flex items-center space-x-3">
                <small>TOKENS</small>
                <h3 class="truncate text-sm font-medium text-gray-900">
                    {{ $account->tokens }}
                </h3>
                <span class="ml-3  sm:block">
                    <button onclick="showModal('modal-transaction')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">
                        <svg  class="-ml-1 mr-2 h-5 w-5 text-gray-400"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" >
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd" />
                        </svg>
                        Add Tokens
                    </button>

                </span>

            </div>
          </div>
        </div>



        </div>
      </div>

      

    </header>
    <div id="modal-transaction" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50" style="display: none;">
    <div  class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <button onclick="closeModal('modal-transaction')" class="float-right text-gray-500 hover:text-gray-800">&times;</button>
        <div>
        <h2 class="text-2xl">Add Tokens</h2>
            <form action="{{ route('accounts.storeTokens') }}" method="post">
            @csrf
            <div class="mb-4">
              <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Number of tokens to add</label>
              <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="amount" name="amount" >
            </div>
            <input type="hidden" id="user_id" name="user_id" value="{{ $account->id }}" >
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">Add Tokens</button>
          </form>

        </div>
    </div>
</div>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
        <form method="get" id="filter-form">
  <select id="type" name="type">
  <option value="">All Types</option>
  <option {{ $filter['type']=='purchase'?'selected' :'' }} value="purchase">Purchase</option>
  <option {{ $filter['type']=='deduct'?'selected' :'' }} value="deduct">Deduct</option>
</select>
<select id="sortby" name="sortby">
  <option value="">Sort By</option>
  <option  {{ $filter['sortby']=='id_desc'?'selected' :'' }}  value="id_desc">Latest to Oldest</option>
  <option  {{ $filter['sortby']=='id_asc'?'selected' :'' }}  value="id_asc">Oldest to Latest</option>
</select>
</form>


@if(count($transactions) > 0)
<div>
        <table class="mt-5 w-full table-fixed">
            <thead>
              <tr class="bg-gray-100">
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Transaction ID</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Amount</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Type</th>
                  <th class="w-1/4 py-4 px-6 text-left text-gray-600 font-bold uppercase">Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($transactions  as $transaction)
                <tr>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $transaction->id }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">{{ $transaction->amount }}</td>
                  <td class="py-4 px-6 border-b border-gray-200">
                    @if($transaction->type=='purchase')
                    <span class="py-1 px-2.5 border-none rounded bg-green-100 text-xl text-green-800 ">PURCHASE</span>
                    @else
                    <span class="py-1 px-2.5 border-none rounded bg-red-100 text-xl text-red-800 ">DEDUCT</span>
                    @endif
                </td>
                <td class="py-4 px-6 border-b border-gray-200">{{ $transaction->created_at }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>


          </div><br/>
    @endif








        </div>
      </div>


    </div>
  </div>
@push('other-scripts')
<script>
var showModal = function (name) {
  console.log(name);
    let elem = document.getElementById(name);
    elem.style.display = "";
};

var closeModal = function (name) {
    let elem = document.getElementById(name);
    elem.style.display = "none";
};
</script>
@endpush
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