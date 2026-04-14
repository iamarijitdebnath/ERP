@if ($paginator->hasPages())
    <nav aria-label="Page navigation example">
        <ul class="flex -space-x-px text-sm">
            @if ($paginator->onFirstPage())
                <li>
                    <span class="flex items-center justify-center text-gray-400 bg-gray-100 box-border border border-gray-300 font-medium rounded-s-lg text-sm px-3 h-9 cursor-not-allowed">Previous</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center text-gray-500 bg-white box-border border border-gray-300 hover:bg-gray-100 hover:text-gray-700 font-medium rounded-s-lg text-sm px-3 h-9 focus:outline-none">Previous</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="flex items-center justify-center text-gray-500 bg-white box-border border border-gray-300 font-medium text-sm w-9 h-9">{{ $element }}</span>
                    </li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span aria-current="page" class="flex items-center justify-center text-blue-600 bg-blue-50 box-border border border-gray-300 hover:text-blue-700 font-medium text-sm w-9 h-9 focus:outline-none">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="flex items-center justify-center text-gray-500 bg-white box-border border border-gray-300 hover:bg-gray-100 hover:text-gray-700 font-medium text-sm w-9 h-9 focus:outline-none">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center text-gray-500 bg-white box-border border border-gray-300 hover:bg-gray-100 hover:text-gray-700 font-medium rounded-e-lg text-sm px-3 h-9 focus:outline-none">Next</a>
                </li>
            @else
                <li>
                    <span class="flex items-center justify-center text-gray-400 bg-gray-100 box-border border border-gray-300 font-medium rounded-e-lg text-sm px-3 h-9 cursor-not-allowed">Next</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
