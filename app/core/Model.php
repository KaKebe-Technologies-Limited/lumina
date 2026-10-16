<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Generic table gateway (rows are plain associative arrays - no hydration, no magic). One thin subclass per table lives in
 * app/models/. Models are FOUNDATION-OWNED and frozen: put extra/complex queries in your own controller or service using
 * App\Core\Database (prepared statements only).
 *
 *   Post::find(5)                                       // ?array
 *   Post::findOrFail(5)                                 // array or HttpException(404)
 *   Post::findBy('slug', 'hello')                       // first row where slug = 'hello'
 *   Post::all('published_at DESC', 10)                  // every row (optional order/limit)
 *   Post::where(['status' => 'published', 'post_type' => 'news'], 'published_at DESC', 6)
 *   Post::first(['slug' => 'x'])                        // first matching row
 *   Post::count(['status' => 'draft'])  Post::exists(['slug' => 'x'])
 *   Post::paginate($request->page(), 9, ['status' => 'published'], 'published_at DESC')   // -> Pagination ($p->items)
 *   Post::create(['title' => 'Hi', 'slug' => 'hi'])     // int id (unknown columns + id/created_at/updated_at are ignored)
 *   Post::update(5, ['title' => 'New'])                 // affected rows
 *   Post::delete(5)                                     // affected rows
 *   Post::uniqueSlug('My title', ignoreId: 5)           // 'my-title', 'my-title-2', ...
 *   Post::pluck('slug', ['status' => 'published'])      // list of values
 *
 * WHERE array syntax:  'col' => value (=) | 'col' => [1,2] (IN) | 'col' => null (IS NULL) | 'col !=' => v | 'col >=' => v
 *   (operators: = != <> < <= > >= LIKE NOT LIKE) | '@raw' => ['published_at <= NOW()', []] (trusted SQL + params).
 * ORDER BY strings are validated ("col", "col DESC", "a ASC, b DESC").
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $order = 'id DESC';

    /** @var array<string,string[]> */
    private static array $columnCache = [];

    public static function table(): string
    {
        return static::$table;
    }

    /** @return string[] column names of the table (cached per request) */
    public static function columns(): array
    {
        $t = static::$table;
        if (!isset(self::$columnCache[$t])) {
            self::$columnCache[$t] = array_map(
                static fn(array $r): string => (string) $r['Field'],
                Database::all('SHOW COLUMNS FROM ' . Database::ident($t))
            );
        }
        return self::$columnCache[$t];
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM ' . Database::ident(static::$table) . ' WHERE id = ? LIMIT 1', [$id]);
    }

    public static function findOrFail(int $id): array
    {
        return self::find($id) ?? throw new HttpException(404, 'Not found');
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        return Database::one('SELECT * FROM ' . Database::ident(static::$table) . ' WHERE ' . Database::ident($column) . ' = ? LIMIT 1', [$value]);
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(?string $orderBy = null, ?int $limit = null): array
    {
        return self::where([], $orderBy, $limit);
    }

    /** @return array<int,array<string,mixed>> */
    public static function where(array $conds = [], ?string $orderBy = null, ?int $limit = null, int $offset = 0): array
    {
        [$sql, $params] = self::buildWhere($conds);
        $q = 'SELECT * FROM ' . Database::ident(static::$table) . $sql . ' ORDER BY ' . self::order($orderBy);
        if ($limit !== null) {
            $q .= ' LIMIT ' . max(0, $limit) . ($offset > 0 ? ' OFFSET ' . $offset : '');
        }
        return Database::all($q, $params);
    }

    public static function first(array $conds = [], ?string $orderBy = null): ?array
    {
        return self::where($conds, $orderBy, 1)[0] ?? null;
    }

    public static function count(array $conds = []): int
    {
        [$sql, $params] = self::buildWhere($conds);
        return (int) Database::value('SELECT COUNT(*) FROM ' . Database::ident(static::$table) . $sql, $params, 0);
    }

    public static function exists(array $conds): bool
    {
        return self::count($conds) > 0;
    }

    public static function paginate(int $page = 1, int $perPage = 12, array $conds = [], ?string $orderBy = null): Pagination
    {
        $p = Pagination::make(self::count($conds), $page, $perPage);
        $p->items = self::where($conds, $orderBy, $p->perPage, $p->offset);
        return $p;
    }

    /** @return list<mixed> */
    public static function pluck(string $column, array $conds = [], ?string $orderBy = null): array
    {
        [$sql, $params] = self::buildWhere($conds);
        $rows = Database::all('SELECT ' . Database::ident($column) . ' AS v FROM ' . Database::ident(static::$table) . $sql . ' ORDER BY ' . self::order($orderBy), $params);
        return array_column($rows, 'v');
    }

    public static function create(array $data): int
    {
        $data = self::filter($data);
        if ($data === []) {
            return Database::insert(static::$table, ['id' => null]);
        }
        return Database::insert(static::$table, $data);
    }

    public static function update(int $id, array $data): int
    {
        return Database::update(static::$table, self::filter($data), 'id = ?', [$id]);
    }

    public static function updateWhere(array $conds, array $data): int
    {
        [$sql, $params] = self::buildWhere($conds);
        $data = self::filter($data);
        if ($data === [] || $sql === '') {
            return 0;                                   // refuse to update EVERY row by accident
        }
        $set = implode(', ', array_map(static fn($c) => Database::ident((string) $c) . ' = ?', array_keys($data)));
        return Database::execute('UPDATE ' . Database::ident(static::$table) . " SET {$set}" . $sql, array_merge(array_values($data), $params));
    }

    public static function delete(int $id): int
    {
        return Database::execute('DELETE FROM ' . Database::ident(static::$table) . ' WHERE id = ?', [$id]);
    }

    public static function deleteWhere(array $conds): int
    {
        [$sql, $params] = self::buildWhere($conds);
        if ($sql === '') {
            return 0;                                   // refuse to empty a table by accident
        }
        return Database::execute('DELETE FROM ' . Database::ident(static::$table) . $sql, $params);
    }

    public static function uniqueSlug(string $text, ?int $ignoreId = null, string $column = 'slug', array $where = []): string
    {
        return Slug::unique(static::$table, $text, $ignoreId, $column, $where);
    }

    // ------------------------------------------------------------------ internals

    /** @return array<string,mixed> only real columns, without id/created_at/updated_at */
    private static function filter(array $data): array
    {
        $cols = array_flip(static::columns());
        unset($cols['id'], $cols['created_at'], $cols['updated_at']);
        return array_intersect_key($data, $cols);
    }

    private static function order(?string $orderBy): string
    {
        $o = $orderBy ?? static::$order;
        if (!preg_match('/^\s*[A-Za-z0-9_.]+(\s+(ASC|DESC))?(\s*,\s*[A-Za-z0-9_.]+(\s+(ASC|DESC))?)*\s*$/i', $o)) {
            throw new \InvalidArgumentException("Illegal ORDER BY: {$o}");
        }
        return $o;
    }

    /** @return array{0:string,1:array<int,mixed>} */
    private static function buildWhere(array $conds): array
    {
        $parts = [];
        $params = [];
        foreach ($conds as $key => $value) {
            if ($key === '@raw') {
                $parts[] = '(' . (string) $value[0] . ')';
                foreach ((array) ($value[1] ?? []) as $p) {
                    $params[] = $p;
                }
                continue;
            }
            if (!preg_match('/^([A-Za-z0-9_.]+)\s*(=|!=|<>|<=|>=|<|>|LIKE|NOT LIKE)?$/i', (string) $key, $m)) {
                throw new \InvalidArgumentException("Illegal WHERE key: {$key}");
            }
            $col = Database::ident($m[1]);
            $op = strtoupper($m[2] ?? '=');
            if ($op === '') {
                $op = '=';
            }
            if ($value === null) {
                $parts[] = $col . (in_array($op, ['!=', '<>'], true) ? ' IS NOT NULL' : ' IS NULL');
            } elseif (is_array($value)) {
                if ($value === []) {
                    $parts[] = in_array($op, ['!=', '<>'], true) ? '1=1' : '1=0';
                } else {
                    $parts[] = $col . (in_array($op, ['!=', '<>'], true) ? ' NOT IN (' : ' IN (') . implode(', ', array_fill(0, count($value), '?')) . ')';
                    foreach ($value as $v) {
                        $params[] = $v;
                    }
                }
            } else {
                $parts[] = "{$col} {$op} ?";
                $params[] = $value;
            }
        }
        return [$parts === [] ? '' : ' WHERE ' . implode(' AND ', $parts), $params];
    }
}
