<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Database;
use App\Core\Slug;

/**
 * PRODUCT rules: who may see a product, what it costs right now, how much of it can be bought, which option choices are valid.
 * Every other shop class asks this class; views never re-implement a rule.
 *
 *   VISIBILITY   status = published AND (no category OR the category is active). Drafts / archived products are never public.
 *   PRICE        regular price; a SALE price counts only when 0 < sale < regular AND now is inside [sale_starts, sale_ends] (NULL = open ended).
 *   STOCK        track_stock = 0: unlimited. Otherwise stock_qty units (units in other people's unpaid orders are already subtracted).
 *                Option values may carry their own `stock` (NULL = no limit of its own); the units available for one choice are
 *                min(product stock, stock of the chosen Size, stock of the chosen Colour ...). Option stock is ignored when track_stock = 0.
 *   OPTIONS      JSON groups  [{"key":"size","label":"Size","required":true,"values":[{"value":"m","label":"M","stock":null,"swatch":null}]}]
 *                (max 4 groups, 30 values each). Cleaned by normaliseOptions(); the cart and the order validate against the DATABASE copy.
 */
final class ProductService
{
    public const SORTS = ['featured', 'newest', 'price_asc', 'price_desc', 'name'];
    /** Product slugs that would collide with the shop's own URLs (/shop/cart, /shop/checkout ...). */
    public const RESERVED_SLUGS = ['cart', 'checkout', 'order', 'orders', 'pay', 'webhook', 'category', 'categories', 'terms', 'search', 'api', 'new', 'create'];
    public const MAX_GROUPS = 4;
    public const MAX_VALUES = 30;

    // ------------------------------------------------------------------------------------------------ visibility / queries

    /** SQL predicate (no parameters) for products the public may see. $alias = table alias in the query. */
    public static function visibleSql(string $alias = 'p'): string
    {
        $a = preg_replace('/[^a-z_]/i', '', $alias) ?: 'p';
        return "{$a}.status = 'published' AND ({$a}.category_id IS NULL OR EXISTS (SELECT 1 FROM shop_categories vc WHERE vc.id = {$a}.category_id AND vc.is_active = 1))";
    }

    /** "Effective price now" as SQL (for sorting). Binds :now once per use: pass ['now' => ...] with nowParam(). */
    private static function effectivePriceSql(string $alias = 'p'): string
    {
        return "(CASE WHEN {$alias}.sale_price IS NOT NULL AND {$alias}.sale_price > 0 AND {$alias}.sale_price < {$alias}.price AND ({$alias}.sale_starts IS NULL OR {$alias}.sale_starts <= :now) AND ({$alias}.sale_ends IS NULL OR {$alias}.sale_ends >= :now2) THEN {$alias}.sale_price ELSE {$alias}.price END)";
    }

    /**
     * The public catalogue page.
     * @param array{category?:string,sort?:string,q?:string,featured?:bool,ids?:list<int>} $filters
     * @return array{items:list<array<string,mixed>>,total:int,page:int,pages:int,perPage:int,sort:string}
     */
    public static function catalogue(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(60, $perPage));
        $sort = in_array($filters['sort'] ?? '', self::SORTS, true) ? (string) $filters['sort'] : 'featured';
        $now = date('Y-m-d H:i:s');
        $where = [self::visibleSql('p')];
        $params = [];
        $cat = (string) ($filters['category'] ?? '');
        if ($cat !== '') {
            $where[] = 'p.category_id = (SELECT id FROM shop_categories WHERE slug = :cat AND is_active = 1 LIMIT 1)';
            $params['cat'] = $cat;
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            $where[] = '(p.name LIKE :q1 OR p.summary LIKE :q2)';
            $params['q1'] = $like;
            $params['q2'] = $like;
        }
        if (!empty($filters['featured'])) {
            $where[] = 'p.is_featured = 1';
        }
        if (isset($filters['ids']) && is_array($filters['ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['ids']), static fn(int $i): bool => $i > 0));
            $where[] = $ids === [] ? '1 = 0' : 'p.id IN (' . implode(',', $ids) . ')';
        }
        $order = match ($sort) {
            'newest'     => 'p.created_at DESC, p.id DESC',
            'price_asc'  => 'eff ASC, p.id DESC',
            'price_desc' => 'eff DESC, p.id DESC',
            'name'       => 'p.name ASC, p.id ASC',
            default      => 'p.is_featured DESC, p.sort_order ASC, p.id DESC',
        };
        $sql = ' FROM shop_products p WHERE ' . implode(' AND ', $where);
        $total = (int) Database::value('SELECT COUNT(*)' . $sql, $params, 0);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);
        $rows = Database::all(
            'SELECT p.*, ' . self::effectivePriceSql('p') . ' AS eff' . $sql . ' ORDER BY ' . $order . ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
            $params + ['now' => $now, 'now2' => $now]
        );
        return ['items' => self::hydrateMany($rows), 'total' => $total, 'page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'sort' => $sort];
    }

    /** One product by id. $visibleOnly false = any status (admin, order history). Hydrated (see hydrate()). */
    public static function find(int $id, bool $visibleOnly = true): ?array
    {
        $sql = 'SELECT p.* FROM shop_products p WHERE p.id = ?' . ($visibleOnly ? ' AND ' . self::visibleSql('p') : '') . ' LIMIT 1';
        $row = Database::one($sql, [$id]);
        return $row === null ? null : self::hydrate($row);
    }

    public static function findBySlug(string $slug, bool $visibleOnly = true): ?array
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            return null;
        }
        $sql = 'SELECT p.* FROM shop_products p WHERE p.slug = ?' . ($visibleOnly ? ' AND ' . self::visibleSql('p') : '') . ' LIMIT 1';
        $row = Database::one($sql, [$slug]);
        return $row === null ? null : self::hydrate($row);
    }

    /** Other visible products: same category first, then featured. @return list<array<string,mixed>> */
    public static function related(array $product, int $limit = 4): array
    {
        $id = (int) $product['id'];
        $cat = isset($product['category_id']) ? (int) $product['category_id'] : 0;
        $rows = Database::all(
            'SELECT p.* FROM shop_products p WHERE p.id <> ? AND ' . self::visibleSql('p')
            . ' ORDER BY (p.category_id <=> ?) DESC, p.is_featured DESC, p.sort_order ASC, p.id DESC LIMIT ' . max(1, min(12, $limit)),
            [$id, $cat > 0 ? $cat : null]
        );
        return self::hydrateMany($rows);
    }

    /**
     * Categories for the chips. @return list<array{id:int,slug:string,name:string,sort_order:int,is_active:bool,count:int}>
     * count = visible products in it (only categories with products are returned when $onlyWithProducts).
     */
    public static function categories(bool $activeOnly = true, bool $onlyWithProducts = false): array
    {
        $rows = Database::all(
            'SELECT c.*, (SELECT COUNT(*) FROM shop_products p WHERE p.category_id = c.id AND p.status = \'published\') AS cnt FROM shop_categories c'
            . ($activeOnly ? ' WHERE c.is_active = 1' : '') . ' ORDER BY c.sort_order ASC, c.id ASC'
        );
        $out = [];
        foreach ($rows as $r) {
            if ($onlyWithProducts && (int) $r['cnt'] === 0) {
                continue;
            }
            $out[] = ['id' => (int) $r['id'], 'slug' => (string) $r['slug'], 'name' => (string) $r['name'], 'sort_order' => (int) $r['sort_order'],
                'is_active' => (int) $r['is_active'] === 1, 'count' => (int) $r['cnt']];
        }
        return $out;
    }

    public static function category(string $slug, bool $activeOnly = true): ?array
    {
        $row = Database::one('SELECT * FROM shop_categories WHERE slug = ?' . ($activeOnly ? ' AND is_active = 1' : '') . ' LIMIT 1', [$slug]);
        return $row === null ? null : ['id' => (int) $row['id'], 'slug' => (string) $row['slug'], 'name' => (string) $row['name'], 'sort_order' => (int) $row['sort_order'], 'is_active' => (int) $row['is_active'] === 1];
    }

    /** A unique product slug from a name. */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Slug::make($text);
        if ($base === '' || in_array($base, self::RESERVED_SLUGS, true)) {
            $text = ($base === '' ? 'product' : $base) . '-product';
        }
        return Slug::unique('shop_products', $text, $ignoreId, 'slug', [], 'product');
    }

    public static function url(array|string $product): string
    {
        $slug = is_array($product) ? (string) ($product['slug'] ?? '') : $product;
        return url('shop/' . $slug);
    }

    // ------------------------------------------------------------------------------------------------ hydration

    /**
     * Product row -> everything a view or the cart needs. Added keys:
     *   url, currency, price_minor, unit_minor (price NOW), on_sale, percent_off, sale_ends, price_text, unit_text,
     *   options (parsed groups), details_list (lines), images (list, see ProductImages::view), cover, hover (second picture or null),
     *   stock (stockInfo()), badges (list of: sale, sold_out, low_stock, featured, sample), can_buy (bool: not sold out), can_book (bool),
     *   category (null|['id','slug','name']).
     * @param array<string,mixed> $row
     * @param list<array<string,mixed>>|null $images pre-loaded picture rows (null = query)
     */
    public static function hydrate(array $row, ?array $images = null, ?\DateTimeInterface $at = null): array
    {
        $id = (int) $row['id'];
        $cur = ShopSettings::currency();
        $imgRows = $images ?? Database::all('SELECT * FROM shop_product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC', [$id]);
        $views = array_map([ProductImages::class, 'view'], $imgRows);
        $price = self::priceInfo($row, $at);
        $stock = self::stockInfo($row);
        $badges = [];
        if ($price['on_sale']) {
            $badges[] = 'sale';
        }
        if ($stock['state'] === 'out') {
            $badges[] = 'sold_out';
        } elseif ($stock['state'] === 'low') {
            $badges[] = 'low_stock';
        }
        if ((int) ($row['is_featured'] ?? 0) === 1) {
            $badges[] = 'featured';
        }
        if ((int) ($row['is_sample'] ?? 0) === 1) {
            $badges[] = 'sample';
        }
        $category = null;
        if (!empty($row['category_id'])) {
            $c = Database::one('SELECT id, slug, name FROM shop_categories WHERE id = ?', [(int) $row['category_id']]);
            $category = $c === null ? null : ['id' => (int) $c['id'], 'slug' => (string) $c['slug'], 'name' => (string) $c['name']];
        }
        $details = [];
        foreach (preg_split('/\R/', (string) ($row['details'] ?? '')) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $details[] = $line;
            }
        }
        return array_merge($row, [
            'url'          => self::url($row),
            'currency'     => $cur,
            'price_minor'  => $price['regular'],
            'unit_minor'   => $price['unit'],
            'on_sale'      => $price['on_sale'],
            'percent_off'  => $price['percent_off'],
            'sale_ends'    => $price['sale_ends'],
            'price_text'   => Money::format($price['regular'], $cur),
            'unit_text'    => Money::format($price['unit'], $cur),
            'options'      => self::parseOptions($row['options'] ?? null),
            'details_list' => $details,
            'images'       => $views,
            'cover'        => $views[0] ?? null,
            'hover'        => $views[1] ?? null,
            'stock'        => $stock,
            'badges'       => $badges,
            'can_buy'      => $stock['state'] !== 'out',
            'can_book'     => (int) ($row['allow_booking'] ?? 0) === 1 && $stock['state'] !== 'out',
            'category'     => $category,
        ]);
    }

    /**
     * Hydrate many rows with two queries instead of one per product. @param list<array<string,mixed>> $rows @return list<array<string,mixed>>
     */
    public static function hydrateMany(array $rows): array
    {
        if ($rows === []) {
            return [];
        }
        $ids = array_map(static fn(array $r): int => (int) $r['id'], $rows);
        $byProduct = [];
        foreach (Database::all('SELECT * FROM shop_product_images WHERE product_id IN (' . implode(',', $ids) . ') ORDER BY is_cover DESC, sort_order ASC, id ASC') as $img) {
            $byProduct[(int) $img['product_id']][] = $img;
        }
        return array_map(static fn(array $r): array => self::hydrate($r, $byProduct[(int) $r['id']] ?? []), $rows);
    }

    // ------------------------------------------------------------------------------------------------ price

    /**
     * Price of a product at a moment. All amounts in minor units.
     * @return array{regular:int,unit:int,on_sale:bool,sale:?int,sale_ends:?string,percent_off:int,save:int}
     */
    public static function priceInfo(array $row, ?\DateTimeInterface $at = null): array
    {
        $regular = Money::fromDb($row['price'] ?? 0);
        $sale = isset($row['sale_price']) && $row['sale_price'] !== null && $row['sale_price'] !== '' ? Money::fromDb($row['sale_price']) : null;
        $now = ($at ?? new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $on = false;
        if ($sale !== null && $sale > 0 && $sale < $regular) {
            $starts = (string) ($row['sale_starts'] ?? '');
            $ends = (string) ($row['sale_ends'] ?? '');
            $on = ($starts === '' || $starts <= $now) && ($ends === '' || $ends >= $now);
        }
        $unit = $on ? (int) $sale : $regular;
        return [
            'regular'     => $regular,
            'unit'        => $unit,
            'on_sale'     => $on,
            'sale'        => $sale,
            'sale_ends'   => $on && (string) ($row['sale_ends'] ?? '') !== '' ? (string) $row['sale_ends'] : null,
            'percent_off' => $on && $regular > 0 ? (int) round((($regular - $unit) * 100) / $regular) : 0,
            'save'        => $on ? $regular - $unit : 0,
        ];
    }

    // ------------------------------------------------------------------------------------------------ stock

    /**
     * @return array{tracked:bool,qty:?int,state:string,low:bool,out:bool}  state: unlimited | in_stock | low | out
     */
    public static function stockInfo(array $row): array
    {
        $tracked = (int) ($row['track_stock'] ?? 0) === 1;
        if (!$tracked) {
            return ['tracked' => false, 'qty' => null, 'state' => 'unlimited', 'low' => false, 'out' => false];
        }
        $qty = max(0, (int) ($row['stock_qty'] ?? 0));
        $out = $qty <= 0;
        if (!$out) {
            foreach (self::parseOptions($row['options'] ?? null) as $g) {
                if (!$g['required']) {
                    continue;
                }
                $any = false;
                foreach ($g['values'] as $v) {
                    if ($v['stock'] === null || $v['stock'] > 0) {
                        $any = true;
                        break;
                    }
                }
                if (!$any && $g['values'] !== []) {
                    $out = true;                       // every size / colour of a required group is gone
                    break;
                }
            }
        }
        $low = !$out && $qty <= max(0, (int) ($row['low_stock'] ?? 0));
        return ['tracked' => true, 'qty' => $qty, 'state' => $out ? 'out' : ($low ? 'low' : 'in_stock'), 'low' => $low, 'out' => $out];
    }

    /**
     * Units that can be bought for one choice of options: null = no limit, else >= 0.
     * @param array<string,string> $selection key => chosen value (already validated)
     */
    public static function maxOrderable(array $row, array $selection = []): ?int
    {
        if ((int) ($row['track_stock'] ?? 0) !== 1) {
            return null;
        }
        $max = max(0, (int) ($row['stock_qty'] ?? 0));
        foreach (self::parseOptions($row['options'] ?? null) as $g) {
            $chosen = $selection[$g['key']] ?? null;
            if ($chosen === null) {
                continue;
            }
            foreach ($g['values'] as $v) {
                if ($v['value'] === $chosen && $v['stock'] !== null) {
                    $max = min($max, max(0, $v['stock']));
                }
            }
        }
        return $max;
    }

    // ------------------------------------------------------------------------------------------------ options

    /**
     * Clean option groups from anything (admin form input or the stored JSON). Never throws; drops what is invalid.
     * Accepts $groups as a list of ['label' => 'Size', 'required' => true, 'values' => ['S', 'M'] | 'S, M, L' | [['label' => 'S', 'stock' => 4], ...]].
     * Keys and values are generated from the labels (lower case a-z 0-9 _ -) and made unique. Stock: whole number >= 0 or null. Swatch: #RRGGBB or null.
     * @return list<array{key:string,label:string,required:bool,values:list<array{value:string,label:string,stock:?int,swatch:?string}>}>
     */
    public static function normaliseOptions(mixed $groups): array
    {
        if (!is_array($groups)) {
            return [];
        }
        $out = [];
        $usedKeys = [];
        foreach ($groups as $g) {
            if (!is_array($g) || count($out) >= self::MAX_GROUPS) {
                continue;
            }
            $label = self::cleanText($g['label'] ?? '', 40);
            if ($label === '') {
                continue;
            }
            $key = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '_', (string) ($g['key'] ?? '')), '_'));
            if ($key === '' || strlen($key) > 30) {
                $key = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower(Slug::make($label))), '_');
            }
            $key = substr($key !== '' ? $key : 'option', 0, 30);
            for ($i = 2; in_array($key, $usedKeys, true); $i++) {
                $key = substr($key, 0, 26) . '_' . $i;
            }
            $vals = $g['values'] ?? [];
            if (is_string($vals)) {
                $vals = preg_split('/[\r\n,]+/', $vals) ?: [];
            }
            $values = [];
            $usedVals = [];
            $usedLabels = [];
            foreach ((array) $vals as $v) {
                if (count($values) >= self::MAX_VALUES) {
                    break;
                }
                $vl = self::cleanText(is_array($v) ? ($v['label'] ?? '') : $v, 40);
                if ($vl === '' || in_array(mb_strtolower($vl), $usedLabels, true)) {
                    continue;                                   // empty or a repeat of a value already listed
                }
                $usedLabels[] = mb_strtolower($vl);
                $val = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', is_array($v) && (string) ($v['value'] ?? '') !== '' ? (string) $v['value'] : Slug::make($vl)), '-'));
                $val = substr($val !== '' ? $val : 'v', 0, 40);
                for ($i = 2; in_array($val, $usedVals, true); $i++) {
                    $val = substr($val, 0, 36) . '-' . $i;
                }
                $usedVals[] = $val;
                $stock = is_array($v) && isset($v['stock']) && $v['stock'] !== '' && $v['stock'] !== null && is_numeric($v['stock']) ? max(0, min(1000000, (int) $v['stock'])) : null;
                $swatch = is_array($v) && isset($v['swatch']) && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v['swatch']) === 1 ? strtolower((string) $v['swatch']) : null;
                $values[] = ['value' => $val, 'label' => $vl, 'stock' => $stock, 'swatch' => $swatch];
            }
            if ($values === []) {
                continue;
            }
            $usedKeys[] = $key;
            $out[] = ['key' => $key, 'label' => $label, 'required' => !array_key_exists('required', $g) || filter_var($g['required'], FILTER_VALIDATE_BOOLEAN), 'values' => $values];
        }
        return $out;
    }

    /** Stored JSON / array -> cleaned groups. @return list<array<string,mixed>> */
    public static function parseOptions(mixed $raw): array
    {
        if (is_string($raw)) {
            $trim = trim($raw);
            if ($trim === '' || $trim === 'null') {
                return [];
            }
            $raw = json_decode($trim, true);
        }
        return self::normaliseOptions($raw);
    }

    /** Groups -> JSON for the options column (null when there are none). */
    public static function encodeOptions(array $groups): ?string
    {
        $clean = self::normaliseOptions($groups);
        return $clean === [] ? null : json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Check the choices a customer made against the product's option groups.
     * @param mixed $posted expected: array key => value (strings); anything else counts as "nothing chosen"
     * @return array{ok:bool,selection:array<string,string>,text:string,errors:array<string,string>}
     *         errors: option key => 'required' | 'invalid' | 'out_of_stock', or '_' => 'unknown' when keys that do not exist were posted
     */
    public static function validateSelection(array $row, mixed $posted): array
    {
        $groups = self::parseOptions($row['options'] ?? null);
        $posted = is_array($posted) ? $posted : [];
        $errors = [];
        $selection = [];
        $parts = [];
        $known = array_column($groups, 'key');
        foreach ($posted as $k => $v) {
            if (!is_string($k) || !in_array($k, $known, true)) {
                $errors['_'] = 'unknown';
            }
        }
        $tracked = (int) ($row['track_stock'] ?? 0) === 1;
        foreach ($groups as $g) {
            $v = $posted[$g['key']] ?? '';
            if (!is_string($v) && !is_int($v)) {
                $v = '';
            }
            $v = trim((string) $v);
            if ($v === '') {
                if ($g['required']) {
                    $errors[$g['key']] = 'required';
                }
                continue;
            }
            $hit = null;
            foreach ($g['values'] as $cand) {
                if ($cand['value'] === $v) {
                    $hit = $cand;
                    break;
                }
            }
            if ($hit === null) {
                $errors[$g['key']] = 'invalid';
                continue;
            }
            if ($tracked && $hit['stock'] !== null && $hit['stock'] <= 0) {
                $errors[$g['key']] = 'out_of_stock';
                continue;
            }
            $selection[$g['key']] = $hit['value'];
            $parts[] = $g['label'] . ': ' . $hit['label'];
        }
        return ['ok' => $errors === [], 'selection' => $selection, 'text' => implode(', ', $parts), 'errors' => $errors];
    }

    /** Plain text, no tags, collapsed spaces, cut to $max characters. */
    private static function cleanText(mixed $v, int $max): string
    {
        if (!is_scalar($v)) {
            return '';
        }
        $s = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $v)));
        $s = str_replace(['<', '>', '"'], '', $s);
        return mb_substr($s, 0, $max);
    }
}
