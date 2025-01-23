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
            <h1 class="text-2xl text-slate-800 dark:text-slate-100 font-bold mb-1">Updates from webhook pull</h1>
              <table  class="mt-5 table-auto w-full ">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Filename</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Total row</th>
                    <th class="px-4 py-2">Processed row</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($updateFiles as $file)
                    <tr style="cursor: pointer" onclick="window.location='{{ route('download_updates_file', $file->id) }}'">
                      <td class="border border-gray-200 px-4 py-2">{{ $file->id }}</td>
                      <td class="border border-gray-200 px-4 py-2">{{ $file->file_name }}</td>
                      <td class="border border-gray-200 px-4 py-2">{{ $file->created_at }}</td>
                        @if(0 === $file->status)
                          <td class="font-bold text-gray-400 px-4 py-2">CREATED</td>
                        @elseif(10 === $file->status)
                            <td class="font-bold text-blue-400 px-4 py-2">PENDING</td>
                        @elseif(20 === $file->status)
                            <td class="font-bold text-blue-400 px-4 py-2">PROCESSING</td>
                        @elseif(30 === $file->status)
                            <td class="font-bold text-green-400 px-4 py-2">COMPLETED</td>
                        @else
                            <td class="font-bold text-red-500 px-4 py-2">FAILED</td>
                        @endif

                      <td class="border border-gray-200 px-4 py-2">{{ $file->total_rows }}</td>
                      <td class="border border-gray-200 px-4 py-2">{{ $file->processed_rows }}</td>
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
</x-app-layout>
