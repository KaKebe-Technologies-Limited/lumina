<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * PDO singleton with small static helpers. ALWAYS use bound parameters (never concatenate user input).
 *
 *   $rows = Database::all('SELECT * FROM posts WHERE status = ? LIMIT ?', ['published', 10]);
 *   $row  = Database::one('SELECT * FROM users WHERE email = :email', ['email' => $email]);
 *   $n    = Database::value('SELECT COUNT(*) FROM posts');
 *   $id   = Database::insert('faqs', ['question' => 'Q', 'answer' => 'A']);
 *   Database::update('faqs', ['answer' => 'B'], 'id = ?', [$id]);
 *   Database::transaction(function () { ... });
 *
 * Prepares are emulated (so the same :name may be used twice and LIMIT ? works); the connection
 * charset is utf8mb4 so this is injection-safe. Integers/bools/null are bound with proper PDO types.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::connect((string) Config::get('db.name'));
        }
        return self::$pdo;
    }

    /** Connect with explicit db name ('' = no database; used by bin/setup.php). */
    public static function connect(string $dbName = ''): PDO
    {
        $host = (string) Config::get('db.host', '127.0.0.1');
        $port = (int) Config::get('db.port', 3306);
        $dsn = "mysql:host={$host};port={$port};" . ($dbName !== '' ? "dbname={$dbName};" : '') . 'charset=utf8mb4';
        $offset = date('P'); // keep MySQL session time zone in sync with the app (Africa/Kampala = +03:00)
        $pdo = new PDO($dsn, (string) Config::get('db.user'), (string) Config::get('db.pass'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '{$offset}', sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'",
        ]);
        return $pdo;
    }

    public static function setConnection(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /** Prepare + execute with typed binding. Accepts positional (?) or named (:x / ['x'=>..]) params. */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $i = 1;
        foreach ($params as $key => $value) {
            $name = is_int($key) ? $i++ : (str_starts_with((string) $key, ':') ? $key : ':' . $key);
            if (is_int($value)) {
                $stmt->bindValue($name, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $stmt->bindValue($name, $value ? 1 : 0, PDO::PARAM_INT);
            } elseif ($value === null) {
                $stmt->bindValue($name, null, PDO::PARAM_NULL);
            } elseif (is_array($value) || is_object($value)) {
                throw new \InvalidArgumentException('Database::run() received a non-scalar parameter for ' . $name);
            } else {
                $stmt->bindValue($name, (string) $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** First column of the first row, or $default when no row. */
    public static function value(string $sql, array $params = [], mixed $default = null): mixed
    {
        $row = self::run($sql, $params)->fetch(PDO::FETCH_NUM);
        return $row === false ? $default : $row[0];
    }

    /** Execute INSERT/UPDATE/DELETE/DDL; returns affected row count. */
    public static function execute(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    /** INSERT from an associative array (column names must be trusted identifiers, never user input). Returns insert id. */
    public static function insert(string $table, array $data): int
    {
        if ($data === []) {
            throw new \InvalidArgumentException('Database::insert() needs at least one column.');
        }
        $cols = array_keys($data);
        $sql = 'INSERT INTO ' . self::ident($table) . ' (' . implode(', ', array_map([self::class, 'ident'], $cols)) . ') VALUES ('
            . implode(', ', array_fill(0, count($cols), '?')) . ')';
        self::run($sql, array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    /** UPDATE from an associative array with a raw WHERE (use ? placeholders in $where). Returns affected rows. */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        if ($data === []) {
            return 0;
        }
        $set = implode(', ', array_map(static fn($c) => self::ident((string) $c) . ' = ?', array_keys($data)));
        return self::execute('UPDATE ' . self::ident($table) . " SET {$set} WHERE {$where}", array_merge(array_values($data), array_values($whereParams)));
    }

    public static function lastId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }

    /** Run $fn inside a transaction; commits on return, rolls back and rethrows on exception. Returns $fn's result. */
    public static function transaction(callable $fn): mixed
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            return $fn();
        }
        $pdo->beginTransaction();
        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /** Back-tick quote an identifier (table/column). Only [A-Za-z0-9_.] allowed. */
    public static function ident(string $name): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/', $name)) {
            throw new \InvalidArgumentException("Illegal SQL identifier: {$name}");
        }
        return '`' . str_replace('.', '`.`', $name) . '`';
    }

    /** True if the table exists in the current database. */
    public static function tableExists(string $table): bool
    {
        return (bool) self::value('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$table]);
    }

    /**
     * Execute a multi-statement SQL script safely (respects quotes and comments). Returns number of statements run.
     * Used by bin/setup.php for schema.sql / seed.sql.
     */
    public static function importSql(string $sql, ?PDO $pdo = null): int
    {
        $pdo ??= self::pdo();
        $count = 0;
        foreach (self::splitSql($sql) as $statement) {
            $pdo->exec($statement);
            $count++;
        }
        return $count;
    }

    /** @return string[] */
    public static function splitSql(string $sql): array
    {
        $statements = [];
        $buf = '';
        $len = strlen($sql);
        $quote = null;
        for ($i = 0; $i < $len; $i++) {
            $c = $sql[$i];
            $n = $i + 1 < $len ? $sql[$i + 1] : '';
            if ($quote !== null) {
                $buf .= $c;
                if ($c === '\\' && $quote !== '`') {
                    $buf .= $n;
                    $i++;
                } elseif ($c === $quote) {
                    if ($n === $quote) {      // doubled quote
                        $buf .= $n;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }
            if ($c === "'" || $c === '"' || $c === '`') {
                $quote = $c;
                $buf .= $c;
            } elseif ($c === '#' || ($c === '-' && $n === '-' && ($i + 2 >= $len || ctype_space($sql[$i + 2])))) {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }
                $buf .= "\n";
            } elseif ($c === '/' && $n === '*' && ($i + 2 >= $len || $sql[$i + 2] !== '!')) {
                $end = strpos($sql, '*/', $i + 2);
                $i = $end === false ? $len : $end + 1;
                $buf .= ' ';
            } elseif ($c === ';') {
                if (trim($buf) !== '') {
                    $statements[] = trim($buf);
                }
                $buf = '';
            } else {
                $buf .= $c;
            }
        }
        if (trim($buf) !== '') {
            $statements[] = trim($buf);
        }
        return $statements;
    }
}
