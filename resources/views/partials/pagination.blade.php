@if ($paginator->hasPages())
    <style>
        .paginator-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-sans);
        }

        .paginator-numbers {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .paginator-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-decoration: none;
            transition: var(--transition-smooth);
            cursor: pointer;
            user-select: none;
        }

        .paginator-btn:hover:not(.disabled):not(.active) {
            border-color: var(--border-hover);
            background-color: var(--table-row-hover);
            color: var(--card-text-highlight);
        }

        .paginator-btn.active {
            background-color: var(--color-indigo);
            border-color: var(--color-indigo);
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .paginator-btn.disabled {
            color: var(--text-muted);
            opacity: 0.5;
            cursor: not-allowed;
            background-color: transparent;
            border-color: var(--border-color);
        }

        .paginator-dots {
            color: var(--text-secondary);
            padding: 0 4px;
            font-size: 13px;
        }

        @media (max-width: 640px) {
            .paginator-wrapper {
                gap: 4px;
            }
            .paginator-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
    </style>

    <div class="paginator-wrapper">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="paginator-btn disabled">&larr; Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="paginator-btn" rel="prev">&larr; Previous</a>
        @endif

        {{-- Pagination Elements --}}
        <div class="paginator-numbers">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="paginator-dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="paginator-btn active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="paginator-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="paginator-btn" rel="next">Next &rarr;</a>
        @else
            <span class="paginator-btn disabled">Next &rarr;</span>
        @endif
    </div>
@endif
