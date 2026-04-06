@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Phân trang" class="flex items-center justify-between gap-2">

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

    </nav>
@endif
