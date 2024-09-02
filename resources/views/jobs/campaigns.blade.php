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
              <li>
                <div class="flex items-center">
                  <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
</svg>
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Campaigns</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Queue by Campaigns</h1>
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
  </div>

  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative">

  <div style="font-size: 6rem" class=" font-bold text-slate-800 dark:text-slate-100 mr-2">{{number_format($params['total_not_downloaded_in_queue']) }} / {{ number_format($params['total_in_queue']) }}</div>
    <p>Messages in Queue</p>
        <div class="m-6">
        </div>

      <div class="mt-5 bg-white overflow-hidden  sm:rounded-lg hidden">
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('jobs.postIndex') }}?_h={{ time() }}" enctype="multipart/form-data" method="post">
              @csrf
              <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Generate Message Exports</div>
              <p>This would generate a csv of deliverable messages which can be downloaded from the table below.</p>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Number of messages</label>
                <select id="number_messages" name="number_messages" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach([50, 100, 250, 500, 1000, 1500, 2000, 5000, 10000, 20000, 30000] as $num)
                  <option value="{{$num}}">{{$num}} messages</option>
                @endforeach
                </select>
              </div>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Short Domains</label>
                <select id="_url_shortener" name="_url_shortener" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach($params['urlShorteners'] as $vv)
                <option value="{{ $vv->name }}">{{ $vv->name }} {{ $vv->campaignShortUrls()->count() == 0 ? '(unused)': '('.$vv->campaignShortUrls()->count().' Camps.)' }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">Download</button>
            </form>
          <br/>
          </div>
        </div>

<div class="  grid grid-cols-2 gap-4 ">
    <div class="bg-white rounded-lg  p-4  overflow-y-auto" style="max-height:600px;" >
        <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2 mb-4">Campaigns</div>
        <div class="">
        @foreach($params['campaigns'] as $campaign)
        <div id="campaign-{{ $campaign->id }}" class="bg-gray-100 border-2 border-white p-4 mb-4 hover:bg-white lnk-campaign cursor-pointer" data-id="{{ $campaign->id }}" >
            <h3 class="mb-1 text-slate-900 font-semibold">
                <span class="mb-1 block text-sm leading-6 text-indigo-500">
                <a href="/accounts/{{ $campaign->user_id }}">Created by: {{ $campaign->user->name }}</a></span><span class="text-xl">{{ $campaign->title}}</span>
            </h3>
            <span class="group inline-flex items-center h-9 rounded-full text-sm font-semibold whitespace-nowrap px-3 focus:outline-none focus:ring-2 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 focus:ring-slate-500 mt-2" >
                <span class="font-semibold text-gray-700">{{ $campaign->total_recipients }} </span> 
                <span class="text-gray-500 ms-1">Recipients</span>
            </span>
            <span class="group inline-flex items-center h-9 rounded-full text-sm font-semibold whitespace-nowrap px-3 focus:outline-none focus:ring-2 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 focus:ring-slate-500 mt-2" >
                <span class="font-semibold text-gray-700">{{ $campaign->total_recipients_sent_to }} </span> 
                <span class="text-gray-500 ms-1">Sent to</span>
            </span>
            <span class="group inline-flex items-center h-9 rounded-full text-sm font-semibold whitespace-nowrap px-3 focus:outline-none focus:ring-2 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 focus:ring-slate-500 mt-2" >
            <span class="font-semibold text-gray-700">{{ $campaign->total_recipients - $campaign->total_recipients_sent_to }} </span> 
                <span class="text-gray-500 ms-1">Unsent to</span>
            </span>
          </div>


        @endforeach
        </div>
    </div>
    <div class="">
    <div id="campaign_box" class="mb-5 bg-white show-when-campaign-clicked hidden shadow-xl sm:rounded-lg ">
    <div class="p-4   bg-white border-b border-gray-200">
    <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Campaign: <span id="_campaign_id"></span></div>
        <dl class="">
    <div class="flex ">
        <dt class="font-semibold text-gray-700 w-1/2">Name</dt>
        <dd class="ml-4 text-gray-600  w-1/2" id="campaign_name" ></dd>
    </div>
    <div class="flex ">
        <dt class="font-semibold text-gray-700  w-1/2">User</dt>
        <dd class="ml-4 text-gray-600  w-1/2"><a href="" id="campaign_user"></a></dd>
    </div>
    <div class="flex ">
        <dt class="font-semibold text-gray-700  w-1/2">Total Recipients</dt>
        <dd class="ml-4 text-gray-600 w-1/2" id="campaign_total_recipients"></dd>
    </div>
    <div class="flex ">
        <dt class="font-semibold text-gray-700  w-1/2">Total Messages Exported</dt>
        <dd class="ml-4 text-gray-600 w-1/2" id="campaign_total_exported"></dd>
    </div>
    <div class="flex ">
        <dt class="font-semibold text-gray-700  w-1/2">Total Messages Pending Export</dt>
        <dd class="ml-4 text-gray-600 w-1/2" id="campaign_total_pending_export"></dd>
    </div>
    <div class="flex ">
        <dt class="font-semibold text-gray-700  w-1/2">Total Messages Sent</dt>
        <dd class="ml-4 text-gray-600 w-1/2" id="campaign_total_sent"></dd>
    </div>
    <div class="flex ">
        <dt class="font-semibold text-gray-700  w-1/2">Total Messages Clicked</dt>
        <dd class="ml-4 text-gray-600 w-1/2" id="campaign_total_clicked"></dd>
    </div>
</dl>


      </div>
    </div>
    <div class="p-4 mb-4 text-sm text-yellow-800 show-if-no-exportable hidden rounded-lg bg-yellow-100 dark:bg-gray-800 dark:text-yellow-300" role="alert">
    <span class="block sm:inline">All messages for this campaign have been exported.</span>
        </div>

    <div  id="frm-generate-csv" class="mb-5 bg-white overflow-hidden show-if-exportable shadow-xl sm:rounded-lg show-when-campaign-clicked hidden">
        <div class="p-4   bg-white border-b border-gray-200">
          <form action="{{ route('jobs.postIndex') }}?_h={{ time() }}" enctype="multipart/form-data" method="post">
              @csrf
              <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Generate Message Exports</div>
              <p>This would generate a csv of deliverable messages which can be downloaded from the table below.</p>
              <div class="mb-4 mt-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Number of messages</label>
                <select id="number_messages" name="number_messages" class="shadow appearance-none border rounded w-half py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" >
                @foreach([50, 100, 250, 500, 1000, 1500, 2000, 5000, 10000, 20000, 30000] as $num)
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
              </div>
              <button type="button" id="btn-generate-csv" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">Download</button>
            </form>
          <br/>
          </div>
        </div>
    <div class="bg-white rounded-lg p-4 relative show-when-campaign-clicked hidden overflow-y-auto"  style="max-height:600px;">
    <div class="text-3xl pt-5 font-bold text-slate-800 dark:text-slate-100 mr-2">Generated Files</div> 
        <table id="data-table" class="mt-5  table-auto w-full ">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2">Filename</th>
            <th class="px-4 py-2">No. Entries</th>
            <th class="px-4 py-2">Sent/Unsent</th>
            <th class="px-4 py-2">Created At</th>
            <th class="px-4 py-2">Operation</th>
          </tr>
        </thead>
        <tbody>
            <tr><td colspan="5" class="p-4 text-center">Select a Campaign to view generated csvs</td></tr>
        </tbody>
        </table>

        </div>
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
            <form id="frm-regenerate-csv" method="post">
            @csrf
            <div class="p-4 md:p-5 space-y-4">
                <p>This will generate a unique csv list with regenerated messages using the spintax and selected short domain below.
                  Once done the messages would be removed from the original csv and shifted to a new csv for download. 
                  Proceed if this is what you intend to do.</p>

                <div class="form-group">
              <label for="message_body" class="block text-gray-700 text-sm font-bold mb-2">Message Body</label>
              <textarea style="min-height:150px" class="shadow appearance-none border h-50 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="message_body" name="message_body" ></textarea>
              <small>Enter content as spintax. <a href="javascript:void(0)" class="inline-flex items-center rounded-md border border-gray-300 bg-white py-1 px-1  font-medium text-gray-700 shadow-sm hover:bg-gray-50 " id="lnk-spintax-preview">Preview</a></small>
            </div>
            <div class="form-group mb-4 hidden" id="spintax-holder">
              <div id="spintax-preview" class="bg-gray-100 border border-gray-300 rounded-lg p-4 shadow-md">

                <a href="javascript:void(0)" id="lnk-clear-spintax-preview">[x]<a>
                  <div>

                  </div>
              </div>
            </div>
                
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
                <button type="button" id="btn-regenerate-csv" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Regenerate</button>
                <button data-modal-hide="default-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
            </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script id="files-template" type="text/x-mustache-template">
<tr>
                <td class="border-b border-gray-200 px-4 py-2">
                  <a href="/download/[[ id ]]">File [[ id ]].csv</a>
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                    [[ total_entries ]]
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                    [[ total_sent ]] / [[ total_unsent ]]
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  <a href="/download/[[ id ]]">
                  [[ created_at_ago ]]
                  </a>
                </td>
                <td class="border-b border-gray-200 px-4 py-2">
                  <div class="inline-flex">
                    <a href="javascript:void(0)" data-batch_id="[[ id ]]" data-modal-target="default-modal" data-modal-toggle="default-modal"  class="btn-batch-regenerate border border-green-500 bg-green-500 text-white rounded-md px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-green-600 focus:outline-none focus:shadow-outline">
                        Regen Unsent
                    </a>
                  </div>
                </td>
              </tr>
    </script>

<script>
  $(function(){

    var JobService = function(){
      this.is_running = false;
      this.interval = 5000;
      this.files_to_observe = [];
      
      this.startService = function(){
        window.setInterval(this.interval, function(){

        })
      }

      this.addBatchFileToObserve = function(file_id){
        this.files_to_observe.push(file_id);
      }

      this.runService = function(){
        if(this.is_running == false){
          return;
        }

        $.ajax({
            url: '/api/jobs/generate-csv', // Replace with your API endpoint
            method: 'POST',
            data: { 
                campaign_id: campaignServiceManager.selected_campaign, 
                type:'campaign',
                number_messages: $('#frm-generate-csv').find('select#number_messages').first().val(), 
                url_shortener: $('#frm-generate-csv').find('select#url_shortener').first().val(), 
            },
            success: function(response) {


            hidePreloader();

            campaignServiceManager.triggerSelectedCampaignClick();
            $.growl.notice({ message: "CSV has started generating" });

            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
        


      }
    }

    var $targetEL = document.getElementById('default-modal');
    const modal_options = {
    placement: 'bottom-right',
    backdrop: 'dynamic',
    backdropClasses:
        'bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-40',
    closable: true,
    onHide: () => {
        console.log('modal is hidden');
    },
    onShow: () => {
        console.log('modal is shown');
    },
    onToggle: () => {
        console.log('modal has been toggled');
    },
};
    console.log({mo:$targetEL});
    var regenModal = null;

    var CampaignService = function(){
        this.selected_campaign = null;
        this.campaign_message = null;
        this.selected_class = '';
        this.selectable_campaign_class = 'lnk-campaign';
        this.campaign_object_id = 'campaign-[id]';
        this.selectable_on_camp_click = 'show-when-campaign-clicked';
        this.unselected_class = 'border-white';

        this.modal = null;


        this.initializeModal = function(){
          this.modal = new Modal(document.getElementById('default-modal'));
        }

        this.showModal = function(){
          if(this.modal == null){
            return;
          }

          this.modal.show();
        }

        this.hideModal = function(){
          if(this.modal == null){
            return;
          }

          this.modal.hide();
        }

        this.getCampaignObjectID = function(campaign_id){
            return this.campaign_object_id.replace('[id]', campaign_id);
        }

        this.setMessage = function(message){
          this.campaign_message = message;
        }

        this.getMessage  = function(){
          return this.campaign_message;
        }

        this.triggerSelectedCampaignClick = function(){
            if(this.selected_campaign == null){
                return;
            }

            $('#'+ this.getCampaignObjectID(this.selected_campaign)).trigger('click');
        }

        this.selectCampaign = function(campaign_id){
            this.unmarkAllCampaignObjects();
            this.selected_campaign = campaign_id;
            this.markSelectedCampaign(campaign_id);
        }

        this.markSelectedCampaign = function(campaign_id){
            $('#'+this.getCampaignObjectID(campaign_id)).removeClass(this.unselected_class);
            $('#'+this.getCampaignObjectID(campaign_id)).addClass(this.selected_class);
        }

        this.unmarkAllCampaignObjects = function(){
            $('.'+this.selectable_campaign_class).removeClass(this.selected_class);
            $('.'+this.selectable_campaign_class).addClass(this.unselected_class);
        }

        this.toggleCampaignAssetsDisplayOnClick = function(campaign_id){
            $('.'+this.selectable_on_camp_click).removeClass('hidden');
        }
    };

    var showPreloader = function(){
      $.LoadingOverlay("show");

        //$('#preloader-overlay').removeClass('hidden');
    }

    var hidePreloader = function(){
      $.LoadingOverlay("hide");
      //$('#preloader-overlay').addClass('hidden');
    }

    var campaignServiceManager = new CampaignService();


    $('body').on('click', '#btn-generate-csv', function(e){
        e.preventDefault();
        showPreloader();
        $.ajax({
            url: '/api/jobs/generate-csv', // Replace with your API endpoint
            method: 'POST',
            data: { 
                campaign_id: campaignServiceManager.selected_campaign, 
                type:'campaign',
                number_messages: $('#frm-generate-csv').find('select#number_messages').first().val(), 
                url_shortener: $('#frm-generate-csv').find('select#url_shortener').first().val(), 
            },
            success: function(response) {


            hidePreloader();

            campaignServiceManager.triggerSelectedCampaignClick();
            $.growl.notice({ message: "CSV has started generating" });

            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });

    });


    $('body').on('click','.btn-batch-regenerate', function(){
      $('#modal_batch').val($(this).data('batch_id'));
      let _m = campaignServiceManager.getMessage();
      $('#message_body').val(_m.body);
      // $('#default-modal').removeClass('hidden');
      campaignServiceManager.showModal();
    });

    
    $('body').on('click', '#btn-regenerate-csv', function(e){
        e.preventDefault();
        showPreloader();
        $.ajax({
            url: '/api/jobs/regenerate-csv', // Replace with your API endpoint
            method: 'POST',
            data: { 
                campaign_id: campaignServiceManager.selected_campaign, 
                type:'campaign',
                message_body: $('#frm-regenerate-csv').find('textarea#message_body').first().val(),
                batch: $('#frm-regenerate-csv').find('input#modal_batch').first().val(),
                number_messages: $('#frm-generate-csv').find('select#number_messages').first().val(), 
                url_shortener: $('#frm-generate-csv').find('select#url_shortener').first().val(), 
            },
            success: function(response) {

            campaignServiceManager.hideModal();

            hidePreloader();

            campaignServiceManager.triggerSelectedCampaignClick();
            $.growl.notice({ message: "CSV has started regenerating" });

            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });

    });

    $('body').on('click', '.lnk-campaign', function(e){
        e.preventDefault();
        // Get the data-id attribute
        var id = $(this).data('id');

        console.log({campaignServiceManager:campaignServiceManager});
        campaignServiceManager.selectCampaign(id);
        showPreloader();
        // AJAX POST request
        $.ajax({
            url: '/api/batch_files', // Replace with your API endpoint
            method: 'POST',
            data: { campaign_id: id },
            success: function(response) {
              campaignServiceManager.initializeModal();
              
                var template = $('#files-template').html();
                        
                        // Clear previous content
                        $('#data-table').find('tbody').empty();
                        
                        // Set Mustache.js delimiters
                        Mustache.tags = ['[[', ']]'];
                        hidePreloader();
                        campaignServiceManager.toggleCampaignAssetsDisplayOnClick(id);
                        campaignServiceManager.setMessage(response.data.message);

                        $('#_campaign_id').html(response.data.campaign.id);
                        $('#campaign_name').html(response.data.campaign.title);
                        
                        $('#campaign_user').html(response.data.campaign.username);
                        $('#campaign_user').prop('href', '/accounts/'+ response.data.campaign.user_id);
                        $('#campaign_total_recipients').html(response.data.campaign.total_recipients.toLocaleString());
                        $('#campaign_total_exported').html(response.data.campaign.total_recipients_in_process.toLocaleString());

                        if(response.data.campaign.total_recipients_in_process == response.data.campaign.total_recipients){
                          $('.show-if-no-exportable').removeClass('hidden');
                          $('.show-if-exportable').addClass('hidden');
                        }else{
                          $('.show-if-no-exportable').addClass('hidden');
                          $('.show-if-exportable').removeClass('hidden');
                        }

                        $('#campaign_total_pending_export').html(response.data.campaign.total_recipients - response.data.campaign.total_recipients_in_process);
                        $('#campaign_total_sent').html(response.data.campaign.total_recipients_sent_to.toLocaleString());
                        $('#campaign_total_clicked').html(response.data.campaign.total_recipients_click_thru.toLocaleString());

                        // Process each post and generate HTML
                        response.data.files.forEach(function(file) {
                            // Render the template with data
                            var html = Mustache.render(template, file);
                            console.log({html:html, file:file});
                            // Append the generated HTML to the posts container
                            $('#data-table').find('tbody').append(html);
                        });

            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
    });
  });


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
