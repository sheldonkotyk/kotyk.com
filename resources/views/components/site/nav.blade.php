@php
    $items = app(\App\Content\ContentRepository::class)->navigation();
    $path = '/'.request()->path();
@endphp
<nav class="bg-gray-800 md:mt-2 md:rounded-lg md:mx-2" aria-label="Main">
    <div x-data="{ showMenu: false }" class="container flex justify-between max-w-(--breakpoint-xl) mx-auto md:justify-start h-14">
        <a href="/" class="flex items-center pr-2 m-2 ml-3 rounded-md cursor-pointer hover:bg-gray-700">
            <img class="w-10 h-10 my-2 mr-4 rounded-md" src="/favicons/apple-icon.png" alt="" width="40" height="40">
            <span class="font-semibold text-gray-50">Sheldon Kotyk</span>
        </a>
        <button @click="showMenu = !showMenu" :aria-expanded="showMenu.toString()" class="block p-2 my-2 mr-2 text-gray-100 rounded-sm md:hidden hover:border focus:border focus:bg-gray-600" type="button" aria-controls="navbar-main" aria-label="Toggle navigation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <ul class="p-2 mx-3 text-base text-gray-900 origin-top md:text-gray-100 md:flex"
            :class="{ 'bg-white border-gray-700 border-b block absolute top-14 w-full p-2 z-20': showMenu, 'hidden': !showMenu }"
            id="navbar-main" x-cloak>
            @foreach ($items as $item)
                @php($current = $path === $item->uri || str_starts_with($path, $item->uri.'/'))
                <li class="border-b border-gray-200 md:border-none md:hover:bg-gray-700 py-3 md:rounded-md md:py-2 md:px-2 my-2 flex items-center mx-3 cursor-pointer md:hover:text-white @if ($current) font-bold @endif" :class="showMenu && 'py-1'">
                    <a href="{{ $item->uri }}" @if ($current) aria-current="page" @endif>{{ $item->title }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
