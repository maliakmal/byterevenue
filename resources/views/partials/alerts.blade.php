    <!-- Success Message -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-3" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';"><title>Close</title><path d="M14.348 14.849a1 1 0 001.415-1.414l-4.829-4.829 4.829-4.829A1 1 0 0014.348 2.93l-4.829 4.829-4.829-4.829A1 1 0 102.93 4.606l4.829 4.829-4.829 4.829a1 1 0 101.414 1.414l4.829-4.829 4.829 4.829z"/></svg>
            </span>
        </div>
    @endif
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 relative" role="alert">
    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';"><title>Close</title><path d="M14.348 14.849a1 1 0 001.415-1.414l-4.829-4.829 4.829-4.829A1 1 0 0014.348 2.93l-4.829 4.829-4.829-4.829A1 1 0 102.93 4.606l4.829 4.829-4.829 4.829a1 1 0 101.414 1.414l4.829-4.829 4.829 4.829z"/></svg>
            </span>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-3" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none';"><title>Close</title><path d="M14.348 14.849a1 1 0 001.415-1.414l-4.829-4.829 4.829-4.829A1 1 0 0014.348 2.93l-4.829 4.829-4.829-4.829A1 1 0 102.93 4.606l4.829 4.829-4.829 4.829a1 1 0 101.414 1.414l4.829-4.829 4.829 4.829z"/></svg>
            </span>
        </div>
    @endif
