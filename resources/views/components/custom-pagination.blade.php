@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            {{-- Mobile Previous --}}
            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true">« Previous</span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="btn btn-sm btn-outline-secondary">« Previous</button>
            @endif

            <span class="small text-muted align-self-center">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            {{-- Mobile Next --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="btn btn-sm btn-outline-secondary">Next »</button>
            @else
                <span class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true">Next »</span>
            @endif
        </div>

        <div class="d-none d-sm-flex align-items-center justify-content-between flex-fill">
            <div>
                <p class="small text-muted mb-0">
                    Showing
                    <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
                    to
                    <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
                    of
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                    products
                </p>
            </div>

            <div>
                <ul class="pagination pagination-sm mb-0 align-items-center gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                            <span class="page-link rounded-2" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px!important;height:14px!important"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" class="page-link rounded-2" rel="prev" aria-label="Previous">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px!important;height:14px!important"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </button>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link border-0">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link rounded-2 fw-bold" style="background:#F97316;border-color:#F97316;color:#fff">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="page-link rounded-2 text-dark">{{ $page }}</button>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" class="page-link rounded-2" rel="next" aria-label="Next">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px!important;height:14px!important"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </button>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                            <span class="page-link rounded-2" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px!important;height:14px!important"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
