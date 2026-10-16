<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Value object returned by Model::paginate() (and usable on its own for custom queries).
 *
 *   $p = Post::paginate($request->page(), 9, ['status' => 'published'], 'published_at DESC');
 *   foreach ($p->items as $row) { ... }
 *   <?= partial('partials/pagination', ['pagination' => $p]) ?>        // keeps the current query string
 *
 *   // custom query:
 *   $p = Pagination::make($total, $request->page(), 12);  $rows = Database::all("... LIMIT ? OFFSET ?", [$p->perPage, $p->offset]);
 *   $p->items = $rows;
 */
final class Pagination
{
    /** @var array<int,array<string,mixed>> */
    public array $items = [];

    public function __construct(
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $pages,
        public readonly int $offset,
    ) {
    }

    public static function make(int $total, int $page, int $perPage): self
    {
        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages && $page > 1 && !(Request::current()?->pathIs('/admin') ?? true)) {
            throw new HttpException(404, 'Not found');       // public lists: ?page=999 is not "page 1 again"
        }
        $page = min(max(1, $page), $pages);
        return new self($total, $page, $perPage, $pages, ($page - 1) * $perPage);
    }

    public function hasPages(): bool
    {
        return $this->pages > 1;
    }

    public function hasPrev(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->pages;
    }

    /** First and last row number on this page (1-based) for "Showing 1-12 of 40". */
    public function from(): int
    {
        return $this->total === 0 ? 0 : $this->offset + 1;
    }

    public function to(): int
    {
        return min($this->total, $this->offset + $this->perPage);
    }

    /**
     * Page numbers to render, with null meaning an ellipsis: [1, null, 4, 5, 6, null, 20].
     * @return array<int,int|null>
     */
    public function window(int $around = 1): array
    {
        $out = [];
        $last = 0;
        for ($i = 1; $i <= $this->pages; $i++) {
            if ($i === 1 || $i === $this->pages || abs($i - $this->page) <= $around) {
                if ($last && $i - $last > 1) {
                    $out[] = null;
                }
                $out[] = $i;
                $last = $i;
            }
        }
        return $out;
    }

    /** URL for page $n keeping the current query string. $base is a path like 'blog' (default: current path). */
    public function url(int $n, ?string $base = null): string
    {
        $query = $_GET;
        $query['page'] = $n;
        if ($n <= 1) {
            unset($query['page']);
        }
        $req = Request::current();
        $path = $base ?? ($req ? ltrim($req->path(), '/') : '');
        return url($path, $query);
    }
}
