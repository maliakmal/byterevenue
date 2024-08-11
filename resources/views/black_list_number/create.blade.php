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
                  <a href="/black-list-numbers" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Black List Number</a>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
</svg>
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Create Black List Number</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Black List Number</h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-8">
          </div>
        </div>
        <div class="mt-5 flex xl:mt-0 xl:ml-4">



        </div>
      </div>
    </header>


  <div class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 xl:flex xl:items-center xl:justify-between">
      <div >
        <div>
        @include('partials.alerts')

        <form action="{{ route('black-list-numbers.store') }}" method="post">
            @csrf
            <div class="mb-4">
              <label for="key" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone_number" name="phone_number" >
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">Create Black List Number</button>
            <a href="/black-list-numbers" class="inline-flex items-center rounded-md border border-gray-300 bg-white py-2 px-4  font-medium text-gray-700 shadow-sm hover:bg-gray-50 ">Back</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>

@push('scripts')
<script>
document.getElementById('lnk-clear-spintax-preview').addEventListener('click', function() {
  document.getElementById('spintax-holder').classList.add('hidden');
  document.querySelector('#spintax-preview div').innerHTML = '';
});

document.getElementById('lnk-spintax-preview').addEventListener('click', function() {
  var matches, options, random;

  var regEx = new RegExp(/{([^{}]+?)}/);
  var text = document.getElementById('message_body').value;
  while ((matches = regEx.exec(text)) !== null) {
    options = matches[1].split('|');
    random = Math.floor(Math.random() * options.length);
    text = text.replace(matches[0], options[random]);
  }
  document.getElementById('spintax-holder').classList.remove('hidden');
  document.querySelector('#spintax-preview div').innerHTML = text;
});
</script>
