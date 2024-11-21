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
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Jobs</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Jobs</h1>
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

    @if($params['download_me']!= null)
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-3" role="alert">
              <span class="block sm:inline">Export file successfully generated - <a href="{{ $params['download_me'] }}" target="_blank">click here</a> to download</span>
              <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';"><title>Close</title><path d="M14.348 14.849a1 1 0 001.415-1.414l-4.829-4.829 4.829-4.829A1 1 0 0014.348 2.93l-4.829 4.829-4.829-4.829A1 1 0 102.93 4.606l4.829 4.829-4.829 4.829a1 1 0 101.414 1.414l4.829-4.829 4.829 4.829z"/></svg>
              </span>
          </div>
    @endif
  </div>

  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div style="font-size: 6rem" class=" font-bold text-slate-800 dark:text-slate-100 mr-2">{{number_format($params['total_not_downloaded_in_queue']) }} / {{ number_format($params['total_in_queue']) }}</div>
              <p>Messages in Queue</p>

      <div class="mt-5 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('jobs.postIndex') }}?_h={{ time() }}" enctype="multipart/form-data" method="post">
              @csrf
              <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Generate Message Exports</div>
              <p>This would generate a csv of deliverable messages which can be downloaded from the table below.</p>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Number of messages</label>
                <select id="number_messages" name="number_messages" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach([100, 300, 500, 1000, 1500, 2000, 5000, 10000, 20000, 30000] as $num)
                  <option value="{{$num}}">{{$num}} messages</option>
                @endforeach
                </select>
              </div>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Short Domains</label>
                <select id="url_shortener" name="url_shortener" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach($params['urlShorteners'] as $vv)
                <option value="{{ $vv->name }}">{{ $vv->name }} {{ $vv->campaignShortUrls()->count() == 0 ? '(unused)': '('.$vv->campaignShortUrls()->count().' Camps.)' }}</option>
                  @endforeach
                </select>
                <input type="hidden" name="type" value="fifo" />
              </div>
              <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">Download</button>
            </form>
          <br/>
          </div>
          </div>

      <div class="mt-5 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">

          <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Downloadable files</div>
      <table  class="mt-5 downloadables table-auto w-full">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2">Filename</th>
            <th class="px-4 py-2">No. Entries</th>
            <th class="px-4 py-2">Campaigns</th>
            <th class="px-4 py-2">UrlShortener</th>
            <th class="px-4 py-2">Created At</th>
            <th class="px-4 py-2">Operation</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($params['files'] as $file)
              <tr>
                <td class="border-b border-gray-200 px-4 py-2" style="cursor: default">
                  @if($file['is_ready'] && $file['number_of_entries'] > 0)
                    <a href="/download/{{$file['id'] }}">File {{ $file['id'] }}.csv
                  @else
                    <span>File {{ $file['id'] }}.csv (preparation)</span>
                  @endif
                  @if(strstr($file['filename'], 'regen'))
                    <br><span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">REGEN {{ $file['prev_batch_id'] ?? '' }}</span>
                  @else
                  @endif
                  @if($file['is_ready'] && $file['number_of_entries'] > 0)
                    </a>
                  @endif
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                {{ $file['number_of_entries'] }}
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                {{ count($file['campaigns']) }}
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  {{ !$file['urlShortener'] ? 'no info' : $file['urlShortener']['name'] }}
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  @if($file['is_ready'] == 1)
                    (pending)
                  @endif
                    {{ $file['created_at']->diffForHumans() }}
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  <div class="inline-flex">
                    <a href="javascript:void(0)" data-batch_id="{{$file['id'] }}" data-modal-target="default-modal" data-modal-toggle="default-modal"  class="btn-batch-regenerate border border-green-500 bg-green-500 text-white rounded-md px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-green-600 focus:outline-none focus:shadow-outline">
                        Regen
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <br/>
        {{ $params['files']->links() }}

        </div>
      </div>
    </div>
  </div>
</x-app-layout>

<div id="default-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Regenerate Unsent Messages
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="default-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form action="/jobs/regenerate" method="post">
            @csrf
            <div class="p-4 md:p-5 space-y-4">
                <p>This will generate a unique csv list with regenerated messages using the selected short domain below. As of now this would create a csv with all unsent messages from the original batch. Once done the messages would be removed from the original csv and shifted to a new csv for download. Proceed if this is what you intend to do.</p>
                <br/>
                <div class="form-group">
                    <label>Domain: </label>
                    <select name="url_shortener" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @foreach($params['urlShorteners'] as $vv)
                            <option value="{{ $vv->name }}">{{ $vv->name }} {{ $vv->campaignShortUrls()->count() == 0 ? '(unused)': '('.$vv->campaignShortUrls()->count().' Camps.)' }}</option>
                        @endforeach
                    </select>
                    <input name="batch" id="modal_batch" type="hidden" />
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Regenerate</button>
                <button data-modal-hide="default-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
            </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.0/jquery-ui.min.js" integrity="sha512-MlEyuwT6VkRXExjj8CdBKNgd+e2H+aYZOCUaCrt9KRk6MlZDOs91V1yK22rwm8aCIsb5Ec1euL8f0g58RKT/Pg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script id="files-template" type="text/x-mustache-template">

<tr id="batch-file-[[ id ]]">
                <td class="border-b border-gray-200 px-4 py-2">
                  <a href="/download/[[ id ]]">File [[ id ]].csv</a>
                  [[ #isRegen ]]
                  <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">REGEN</span>
                  [[ /isRegen ]]
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                [[ number_of_entries ]]
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                [[ campaign_count ]]
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  <a href="/download/[[ id ]]">
                    [[ ^is_ready ]]
                      (pending)
                    [[ /is_ready ]]
                    [[ created_at_ago ]]
                  </a>
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  <div class="inline-flex">
                  [[ #is_ready ]]
                    <a href="javascript:void(0)" data-batch_id="[[ id ]]" data-modal-target="default-modal" data-modal-toggle="default-modal"  class="btn-batch-regenerate border border-green-500 bg-green-500 text-white rounded-md px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-green-600 focus:outline-none focus:shadow-outline">
                      Regen
                    </a>
                    [[ /is_ready ]]

                  </div>
                </td>
              </tr>
    </script>


<script>
  $(function(){
    $('.btn-batch-regenerate').click(function(){
      $('#modal_batch').val($(this).data('batch_id'));
      $('#default-modal').removeClass('hidden');
    });
  });
    @if($params['download_me']!= null)
    $('.downloadables:first li:first-child').toggle( "highlight" );
// function downloadURI(uri, name) {
//   var link = document.createElement("a");
//   link.download = name; // <- name instead of 'name'
//   link.href = uri;

//   link.click();
//   link.remove();
// }

// downloadURI('{{ $params['download_me'] }}', '{{ $params['download_me'] }}');


    @endif
</script>


<script>
  $(function(){

    var JobService = function(){
      this.is_running = false;
      this.interval = 5000;
      this.files_to_observe = [];
      
      this.startService = function(){
        if(this.is_running == true){
          return;
        }
        if(this.files_to_observe.length == 0){
          return;
        }

        this.is_running = true;
        var _this = this;
        window.setInterval( function(){
          _this.probeObservableFiles();
        },this.interval);
      }

      this.probeObservableFiles = function(){
        var _this = this;
        console.log('ping');
        var template = $('#files-template').html();
        $.ajax({
            url: '/api/batch_files/check-status', // Replace with your API endpoint
            method: 'POST',
            data: { 
                files: this.files_to_observe, 
            },
            success: function(response) {
              if(response.data.length == 0){
                return;
              }

              var files_ready = [];
              for(let i = 0; i< response.data.length; i++){
                files_ready.push(response.data[i].id);
                _this.removeBatchFileToObserve(response.data[i].id);
                // Render the template with data
                var html = Mustache.render(template, response.data[i]);
                // Append the generated HTML to the posts container
                $('#data-table').find('#batch-file-'+response.data[i].id).replaceWith(html);
                $('#batch-file-'+response.data[i].id).effect("highlight", {}, 3000);
              }

              $.growl.notice({ message: "CSV file(s) "+(files_ready.join(','))+" are ready" });

            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });

      }



      this.addBatchFileToObserve = function(file_id){
        this.files_to_observe.push(file_id);
      }

      this.removeBatchFileToObserve = function(file_id){
        this.files_to_observe.splice(this.files_to_observe.indexOf(file_id), 1);
      }

      this.runService = function(){
        if(this.is_running == true){
          return;
        }

      }
    }

    var jobService = new JobService();

    <?php
    if(isset($params['files_to_observe']) && count($params['files_to_observe'])>0):
      foreach($params['files_to_observe'] as $file_id):
        ?>
        jobService.addBatchFileToObserve({{ $file_id }});      
        <?php
      endforeach;
      ?>
      jobService.startService();      
      <?php
    endif;
    ?>



    var showPreloader = function(){
      $.LoadingOverlay("show");
    }

    var hidePreloader = function(){
      $.LoadingOverlay("hide");
    }


    $('body').on('click', '#btn-generate-csv', function(e){
      e.preventDefault();
      var template = $('#files-template').html();
      showPreloader();
      $.ajax({
        url: '/api/jobs/generate-csv', // Replace with your API endpoint
        method: 'POST',
        data: { 
          type:'fifo',
          number_messages: $('#frm-generate-csv').find('select#number_messages').first().val(), 
          url_shortener: $('#frm-generate-csv').find('select#url_shortener').first().val(), 
        },
        success: function(response) {
          hidePreloader();

          $.growl.notice({ message: "CSV has started generating" });
          var html = Mustache.render(template, response.data);
          $('#data-table').find('tbody').prepend(html);
          
          jobService.addBatchFileToObserve(response.data.id);
          jobService.startService();
        },
        error: function(xhr, status, error) {
          console.error('An error occurred:', error);
        }
      });
    });

    
  });




</script>

