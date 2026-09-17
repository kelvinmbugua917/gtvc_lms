<?php

namespace App\Core;

class Paginator
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;
    private string $pageParam;

    public function __construct(int $totalItems, int $perPage = 15, int $currentPage = 1, string $pageParam = 'page')
    {
        $this->totalItems = max(0, $totalItems);
        $this->perPage = max(1, $perPage);
        $this->totalPages = (int)ceil($this->totalItems / $this->perPage);
        $this->currentPage = ($this->totalPages > 0) ? min(max(1, $currentPage), $this->totalPages) : 1;
        $this->pageParam = $pageParam;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getStartIndex(): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }
        return $this->getOffset() + 1;
    }

    public function getEndIndex(): int
    {
        return min($this->getOffset() + $this->perPage, $this->totalItems);
    }

    public function hasPages(): bool
    {
        return $this->totalPages > 1;
    }

    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->totalItems,
            'page' => $this->currentPage,
            'per_page' => $this->perPage,
            'total_pages' => $this->totalPages,
            'has_next' => $this->hasNext(),
            'has_prev' => $this->hasPrev(),
            'start' => $this->getStartIndex(),
            'end' => $this->getEndIndex(),
        ];
    }

    public function pageUrl(int $page): string
    {
        $params = $_GET;
        $params[$this->pageParam] = $page;
        $query = http_build_query($params);
        $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
        return $path . ($query ? '?' . $query : '');
    }

    public function slice(array $items): array
    {
        return array_slice($items, $this->getOffset(), $this->getLimit());
    }

    public function render(string $containerClass = 'pagination-container'): string
    {
        if ($this->totalItems === 0) {
            return '';
        }

        $start = $this->getStartIndex();
        $end = $this->getEndIndex();
        $total = $this->totalItems;
        $curr = $this->currentPage;
        $last = $this->totalPages;

        $html = '<div class="' . htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<div class="pagination-info">';
        $html .= 'Showing <strong>' . $start . '</strong> to <strong>' . $end . '</strong> of <strong>' . number_format($total) . '</strong> entries';
        $html .= '</div>';

        if ($last > 1) {
            $html .= '<nav class="pagination-nav" aria-label="Page navigation">';
            
            if ($curr > 1) {
                $html .= '<a href="' . htmlspecialchars($this->pageUrl($curr - 1), ENT_QUOTES, 'UTF-8') . '" class="pagination-btn pagination-prev" aria-label="Previous page">‹ Prev</a>';
            } else {
                $html .= '<span class="pagination-btn pagination-prev disabled" aria-disabled="true">‹ Prev</span>';
            }

            $range = [];
            if ($last <= 7) {
                $range = range(1, $last);
            } else {
                if ($curr <= 4) {
                    $range = [1, 2, 3, 4, 5, '...', $last];
                } elseif ($curr >= $last - 3) {
                    $range = [1, '...', $last - 4, $last - 3, $last - 2, $last - 1, $last];
                } else {
                    $range = [1, '...', $curr - 1, $curr, $curr + 1, '...', $last];
                }
            }

            foreach ($range as $page) {
                if ($page === '...') {
                    $html .= '<span class="pagination-ellipsis">…</span>';
                } elseif ($page === $curr) {
                    $html .= '<span class="pagination-btn active" aria-current="page">' . $page . '</span>';
                } else {
                    $html .= '<a href="' . htmlspecialchars($this->pageUrl((int)$page), ENT_QUOTES, 'UTF-8') . '" class="pagination-btn">' . $page . '</a>';
                }
            }

            if ($curr < $last) {
                $html .= '<a href="' . htmlspecialchars($this->pageUrl($curr + 1), ENT_QUOTES, 'UTF-8') . '" class="pagination-btn pagination-next" aria-label="Next page">Next ›</a>';
            } else {
                $html .= '<span class="pagination-btn pagination-next disabled" aria-disabled="true">Next ›</span>';
            }

            $html .= '</nav>';
        }

        $html .= '</div>';
        return $html;
    }
}
