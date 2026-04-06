@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Phân trang" class="isolate">

        <div class="flex items-center justify-between gap-2 sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-sky-50/50 px-4 py-2 text-sm font-medium leading-5 text-slate-300">
                    Trước
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium leading-5 text-bcom-blue shadow-sm transition hover:bg-sky-50">
                    Trước
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium leading-5 text-bcom-blue shadow-sm transition hover:bg-sky-50">
                    Sau
                </a>
            @else
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-sky-50/50 px-4 py-2 text-sm font-medium leading-5 text-slate-300">
                    Sau
                </span>
            @endif

        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between sm:gap-4">

            <div>
                <p class="text-sm leading-5 text-gray-600">
                    @if ($paginator->firstItem())
                        Hiển thị
                        <span class="font-medium text-bcom-navy">{{ $paginator->firstItem() }}</span>
                        đến
                        <span class="font-medium text-bcom-navy">{{ $paginator->lastItem() }}</span>
                        trong tổng số
                        <span class="font-medium text-bcom-navy">{{ $paginator->total() }}</span>
                        kết quả
                    @else
                        Hiển thị <span class="font-medium text-bcom-navy">{{ $paginator->count() }}</span> kết quả
                    @endif
                </p>
            </div>

            <div>
                <span class="inline-flex rtl:flex-row-reverse">

                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Trang trước">
                            <span class="inline-flex items-center rounded-l-lg border border-slate-200 bg-sky-50/50 px-2 py-2 text-slate-300" aria-hidden="true">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-l-lg border border-slate-200 bg-white px-2 py-2 text-bcom-blue shadow-sm transition hover:bg-sky-50" aria-label="Trang trước">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="-ml-px inline-flex items-center border border-slate-200 bg-white px-4 py-2 text-sm font-medium leading-5 text-slate-400">{{ $element }}</span>
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="-ml-px inline-flex items-center border border-bcom-blue bg-bcom-blue px-4 py-2 text-sm font-semibold leading-5 text-white shadow-sm">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="-ml-px inline-flex items-center border border-slate-200 bg-white px-4 py-2 text-sm font-medium leading-5 text-bcom-navy shadow-sm transition hover:bg-sky-50" aria-label="Đến trang {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="-ml-px inline-flex items-center rounded-r-lg border border-slate-200 bg-white px-2 py-2 text-bcom-blue shadow-sm transition hover:bg-sky-50" aria-label="Trang sau">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Trang sau">
                            <span class="-ml-px inline-flex items-center rounded-r-lg border border-slate-200 bg-sky-50/50 px-2 py-2 text-slate-300" aria-hidden="true">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
