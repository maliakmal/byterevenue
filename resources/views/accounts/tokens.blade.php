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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $account->name }} Tokens</a> 
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Token History 
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

            </div>
          </div>
        </div>



        </div>
      </div>

      

    </header>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
        <form method="get" id="filter-form">
  <select id="type" name="type">
  <option value="">All Types</option>
  <option {{ $filter['type']=='purchase'?'selected' :'' }} value="purchase">Purchase</option>
  <option {{ $filter['type']=='usage'?'selected' :'' }} value="usage">Deduct</option>
</select>
<select id="sortby" name="sortby">
  <option value="">Sort By</option>
  <option  {{ $filter['sortby']=='id_desc'?'selected' :'' }}  value="id_desc">Latest to Oldest</option>
  <option  {{ $filter['sortby']=='id_asc'?'selected' :'' }}  value="id_asc">Oldest to Latest</option>
</select>
</form>

        @if(count($transactions) > 0)
      <div>
        <table class=" mt-5 w-full table-fixed">
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
                    <span class="py-1 px-2.5 border-none rounded bg-green-100  text-green-800 ">PURCHASE</span>
                    @else
                    <span class="py-1 px-2.5 border-none rounded bg-red-100  text-red-800 ">DEDUCT</span>
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
