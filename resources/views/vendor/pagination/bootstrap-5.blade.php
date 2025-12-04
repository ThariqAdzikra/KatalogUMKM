@if ($paginator->hasPages())
    <div class="d-flex flex-column align-items-center mt-4">

        {{-- Info jumlah data --}}
        <p class="pagination-info mb-3">
            Showing <strong>{{ $paginator->firstItem() }}</strong>
            to <strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong> results
        </p>

        {{-- Pagination Links --}}
        <nav>
            <ul class="pagination pagination-premium mb-0">
                {{-- Previous Button --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="bi bi-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Button --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif

<style>
/* Pagination Info Text */
.pagination-info {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.875rem;
    letter-spacing: 0.3px;
}

.pagination-info strong {
    color: #3b82f6;
    font-weight: 600;
}

/* Premium Pagination Container */
.pagination-premium {
    display: flex;
    gap: 0.5rem;
}

/* Page Items */
.pagination-premium .page-item .page-link {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.9);
    padding: 0.5rem 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 40px;
    text-align: center;
    font-size: 0.9rem;
}

.pagination-premium .page-item .page-link:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: rgba(59, 130, 246, 0.3);
    color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

/* Active Page */
.pagination-premium .page-item.active .page-link {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-color: #3b82f6;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    transform: translateY(-2px);
}

/* Disabled State */
.pagination-premium .page-item.disabled .page-link {
    background: rgba(255, 255, 255, 0.02);
    border-color: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.3);
    cursor: not-allowed;
    opacity: 0.5;
}

.pagination-premium .page-item.disabled .page-link:hover {
    transform: none;
    box-shadow: none;
}

/* Navigation Arrows */
.pagination-premium .page-link i {
    font-size: 0.875rem;
    line-height: 1;
}

/* Responsive */
@media (max-width: 576px) {
    .pagination-premium {
        gap: 0.25rem;
    }
    
    .pagination-premium .page-item .page-link {
        padding: 0.4rem 0.65rem;
        min-width: 36px;
        font-size: 0.85rem;
    }
    
    .pagination-info {
        font-size: 0.8rem;
    }
}
</style>
