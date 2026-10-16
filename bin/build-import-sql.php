<?php
declare(strict_types=1);

/**
 * Builds ONE file for phpMyAdmin / hosting panels: database/lumina-live-import.sql = schema.sql + seed.sql.
 *
 *   /c/xampp/php/php.exe bin/build-import-sql.php
 *
 * Import it into an EMPTY (or existing) database that your host already created: it contains no CREATE DATABASE / USE,
 * no DROP, no TRUNCATE and no users. It is safe to import more than once (CREATE TABLE IF NOT EXISTS, idempotent seed).
 * The first administrator is created afterwards through https://your-domain/install (see README).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$parts = [
    'schema' => $root . '/database/schema.sql',
    'seed'   => $root . '/database/seed.sql',
];
$out = $root . '/database/lumina-live-import.sql';

$strip = static function (string $sql): string {
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;          // BOM
    return str_replace(["\r\n", "\r"], "\n", $sql);                    // LF endings
};

$body = '';
foreach ($parts as $name => $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing {$file}\n");
        exit(1);
    }
    $sql = $strip((string) file_get_contents($file));
    if (preg_match('/^\s*(CREATE\s+DATABASE|USE\s+|DROP\s+(DATABASE|TABLE)|TRUNCATE\s)/im', $sql, $m)) {
        fwrite(STDERR, "Refusing: {$name}.sql contains a statement unsuitable for a shared host import: " . trim($m[0]) . "\n");
        exit(1);
    }
    $body .= "\n-- ======================================================================\n-- PART: {$name}.sql\n-- ======================================================================\n\n" . $sql . "\n";
}

$header = "-- Lumina Africa Foundation - database import (schema + starter content)\n"
    . "-- Import into the database your host created (phpMyAdmin > select the database > Import). Safe to import more than once.\n"
    . "-- Contains NO users: create the first admin at /install (README, section Deploy).\n"
    . "SET NAMES utf8mb4;\n";

file_put_contents($out, $header . $body);
echo 'Wrote ' . $out . ' (' . number_format((int) filesize($out)) . " bytes)\n";
