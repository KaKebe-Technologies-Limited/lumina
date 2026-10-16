<?php
declare(strict_types=1);

namespace App\Services\Inbox;

/**
 * Tiny WHERE builder for the admin inbox lists. Only ever receives developer-written SQL fragments with `?` placeholders;
 * every user-supplied value goes through the params list (prepared statements).
 *
 *   $w = (new Where())->add('m.status = ?', [$status])->addLike(['m.name', 'm.email'], $term);
 *   Database::all('SELECT * FROM contact_messages m' . $w->sql(), $w->params());
 */
final class Where
{
    /** @var string[] */
    private array $parts = [];
    /** @var array<int,mixed> */
    private array $params = [];

    /** @param array<int,mixed> $params */
    public function add(string $sql, array $params = []): self
    {
        $this->parts[] = '(' . $sql . ')';
        foreach ($params as $p) {
            $this->params[] = $p;
        }
        return $this;
    }

    /** Match $term (already escaped, e.g. by Support::like()) against any of the columns. @param string[] $columns */
    public function addLike(array $columns, string $likeTerm): self
    {
        if ($likeTerm === '' || $likeTerm === '%%') {
            return $this;
        }
        $or = [];
        $params = [];
        foreach ($columns as $col) {
            $or[] = $col . ' LIKE ?';
            $params[] = $likeTerm;
        }
        return $this->add(implode(' OR ', $or), $params);
    }

    public function isEmpty(): bool
    {
        return $this->parts === [];
    }

    /** ' WHERE a AND b' or '' */
    public function sql(): string
    {
        return $this->parts === [] ? '' : ' WHERE ' . implode(' AND ', $this->parts);
    }

    /** @return array<int,mixed> */
    public function params(): array
    {
        return $this->params;
    }

    /** Copy with additional conditions (the original stays untouched). */
    public function with(string $sql, array $params = []): self
    {
        $c = clone $this;
        return $c->add($sql, $params);
    }
}
