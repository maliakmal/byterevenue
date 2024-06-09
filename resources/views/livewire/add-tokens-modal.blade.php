<div x-data="{ open: @entangle('showModal') }" x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50" style="display: none;">
    <div @click.away="open = false" class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <button @click="open = false" class="float-right text-gray-500 hover:text-gray-800">&times;</button>
        <div>
        <h2 class="text-2xl">Add Tokens</h2>
            <form action="{{ route('accounts.storeTokens') }}" method="post">
            @csrf
            <div class="mb-4">
              <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Number of tokens to add</label>
              <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="amount" name="amount" >
            </div>
            <input type="hidden" id="user_id" name="user_id" value="{!! $account_id !!}" >
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">Add Tokens</button>
          </form>

        </div>
    </div>
</div>
</div>