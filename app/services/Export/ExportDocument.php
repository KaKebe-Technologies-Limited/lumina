<?php
declare(strict_types=1);

namespace App\Services\Export;

/**
 * What every export (PDF, Excel, Word) prints: the standard letterhead (logo, organisation, address, e-mail, phones, website), the title, a few lines about the export
 * (who made it, when, which filters, how many rows) and the table. Built by Exporter from the same header + rows the CSV export uses.
 */
final class ExportDocument
{
    /** @param list<string> $header @param list<list<string>> $rows @param list<string> $notes lines printed under the title */
    public function __construct(
        public readonly string $title,
        public readonly array $header,
        public readonly array $rows,
        public readonly array $letterhead,
        public readonly array $notes = [],
        public readonly string $generated = '',
        public readonly bool $truncated = false,
        public readonly int $total = 0,
    ) {
    }

    public function columns(): int
    {
        return count($this->header);
    }

    /** "28 October 2026, 19:30" */
    public static function now(): string
    {
        return date('j F Y, H:i');
    }
}
