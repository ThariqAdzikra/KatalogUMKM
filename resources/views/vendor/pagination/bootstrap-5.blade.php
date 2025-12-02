@if ($paginator->hasPages())
    <div class="d-flex flex-column align-items-center mt-3">

        {{-- 🟤 Info jumlah data (DIPINDAH KE ATAS) --}}
        <p class="text-muted mb-2 small">
            Showing <strong>{{ $paginator->firstItem() }}</strong>
            to <strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong> results
        </p>

        {{-- 🔽 Pagination Links di bawahnya --}}
        <nav>
            <ul class="pagination pagination-sm mb-0">
                {{-- Tombol Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link border-0 text-secondary bg-transparent">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link border-0 text-brown" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
                    </li>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link border-0">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active">
                                    <span class="page-link border-0 text-white" style="background-color:#9b7a5c;">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link border-0 text-brown" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Tombol Berikutnya --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link border-0 text-brown" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link border-0 text-secondary bg-transparent">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif

<style>
.text-brown {
    color: #9b7a5c !important;
}
.page-item .page-link {
    border-radius: 8px;
    margin: 0 2px;
    background-color: #f8f6f4;
    transition: all 0.2s ease;
}
.page-item .page-link:hover {
    background-color: #e6ddd5;
    color: #9b7a5c;
}
.page-item.active .page-link {
    border: none;
    color: #fff !important;
    background-color: #9b7a5c !important;
}
</style>
