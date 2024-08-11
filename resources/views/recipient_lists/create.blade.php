<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('') }}
    </h2>
  </x-slot>
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
                  <a href="/recipient_lists" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Recipient Lists</a>
                </div>
              </li>
              <li>
                <div class="flex items-center">
                  <svg class="h-5 w-5 flex-shrink-0 text-gray-400" x-description="Heroicon name: mini/chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"></path>
                  </svg>
                  <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Create</a>
                </div>
              </li>
            </ol>
          </nav>
          <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Create Recipient List</h1>
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
        <div class="p-6 py-7 sm:px-20 bg-white border-b border-gray-200">
          <form action="{{ route('recipient_lists.store') }}" id="submit_form" enctype="multipart/form-data" method="post">
            @csrf
            <div class="mb-4 mt-4">
              <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name of List</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" >
            </div>
            <div class="mb-4">
              <livewire:toggle-contacts-textarea-fileinput radio-input-change="changeRadioButton()" on-change="changeCSV()" textarea-name="numbers" selector-name="entry_type" file-input-name="csv_file" />
            </div>
              <div style="margin-top: 50px;display: none" id="csv-table-holder">
                  <h1> <b>Uploaded CSV Data </b></h1>
                  <h3 style="color: red">choose selected proper columns</h3>

                  <div class="flex mb-4">
                      <div class="w-1/3 h-12" style="padding: 10px 5px">
                          <label>name</label>
                          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name_column">
                          </select>
                      </div>
                      <div class="w-1/3 h-12" style="padding: 10px 5px">
                          <label>email</label>
                          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email_column"></select>
                      </div>
                      <div class="w-1/3 h-12" style="padding: 10px 5px">
                          <label>phone</label>
                          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone_column"></select>

                      </div>
                  </div>
                  <div style="margin-top: 50px">
                     <table id="csvTable"></table>
                  </div>
                  <button onclick="submitForm()" type="button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-50">Create Recipient List</button>
                  </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
 <script>

     var csvTable = null;
     function changeCSV(){
         var file = document.getElementsByName('csv_file')[0].files[0];
         if(!file){
             return;
         }
         var reader = new FileReader();
         reader.onload = function () {
             var content = reader.result;
             var parsedCSV = null;
             try {
                 parsedCSV = parseCSV(content);
             }catch (e) {
                 alert('error parse csv')
                 return;
             }
             initTable(parsedCSV);


         };
         reader.onerror = function () {
             console.error('Error reading the file');
         };
         reader.readAsText(file, 'utf-8');
     }
     function parseCSV(content){
         var lines = content.split('\n');
         var headers = lines[0].split(',');
         var i;
         var data = [];
         for(i=1;i<lines.length;i++){
             var row_string = lines[i];
             if(row_string=='') continue;
             var parse_row = row_string.split(',');
             if(headers.length != parse_row.length){
                 throw new Error('error parse row '+i);
             }
             var obj = {};
             var j;
             for(j=0;j<parse_row.length;j++){
                 var key = headers[j].trim();
                 var value = parse_row[j].trim();
                 obj[key] = value;
             }
             data.push(obj);
         }
         return data;
     }
     function initTable(data){
         if(csvTable != null){
             destoryTable();
         }
         var keys = Object.keys(data[0]);
         console.log({keys:keys});
         var columns = [];
         var i;
         for (i=0;i<keys.length;i++) {
             var obj = {};
             obj['data'] = keys[i];
             obj['title'] = keys[i];
             console.log({obj:obj});
             columns.push(obj);
         }
         initializeTable(data,columns);
         fillCSVSelectTags(keys);

     }
     function initializeTable(data,columns){
         $('#csv-table-holder').show();
         csvTable =  $('#csvTable').DataTable({
             data: data,
             columns: columns
         });
     }
     function  destoryTable(){
         csvTable.destroy();
         csvTable = null;
         $('#csvTable').empty();
         $('#csv-table-holder').hide();
     }
     function changeRadioButton(){
         var data = $('#submit_form').serializeArray();
         var i;
         for(i=0;i<data.length;i++){
             var item = data[i];
             if(item.name == "entry_type"){
                 if(item.value == 'file'){
                     $('#csv-table-holder').show();
                 }else{
                     if(csvTable) {
                         destoryTable();
                     }
                     $('#csv-table-holder').hide();
                 }
             }
         }
     }
     function fillCSVSelectTags(options){
         var i;
         var option_tag = '';
         for(i=0;i<options.length;i++){
             var data = options[i];
             option_tag += "<option value='"+i+"'>"+data+"</option>";
         }
         $('#name_column').empty();
         $('#email_column').empty();
         $('#phone_column').empty();

         $('#name_column').append(option_tag);
         $('#email_column').append(option_tag);
         $('#phone_column').append(option_tag);

         $('#name_column').val('name').change();
         $('#email_column').val('email').change();
         $('#phone_column').val('phone').change();
     }
     function submitForm(){
         if(!$('#phone_column').val()){
             alert('Choosing phone number column is mandatory');
             return;
         }

         $('#submit_form').submit();
     }
 </script>
