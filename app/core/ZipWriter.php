<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal ZIP writer in pure PHP (no ZipArchive needed), enough for Office files (.xlsx, .docx): UTF-8 names, deflate when it helps, stored otherwise.
 *
 *   $z = new ZipWriter();  $z->add('word/document.xml', $xml);  $bytes = $z->output();
 */
final class ZipWriter
{
    /** @var list<array{name:string,crc:int,csize:int,size:int,method:int,offset:int}> */
    private array $entries = [];
    private string $data = '';
    private int $dosTime;
    private int $dosDate;

    public function __construct(?int $timestamp = null)
    {
        $t = getdate($timestamp ?? time());
        $year = max(1980, (int) $t['year']);
        $this->dosTime = ((int) $t['hours'] << 11) | ((int) $t['minutes'] << 5) | ((int) $t['seconds'] >> 1);
        $this->dosDate = (($year - 1980) << 9) | ((int) $t['mon'] << 5) | (int) $t['mday'];
    }

    public function add(string $name, string $content): void
    {
        $name = str_replace('\\', '/', ltrim($name, '/'));
        $size = strlen($content);
        $crc = crc32($content) & 0xFFFFFFFF;
        $method = 0;
        $stored = $content;
        if ($size > 64 && function_exists('gzdeflate')) {
            $deflated = gzdeflate($content, 6);
            if ($deflated !== false && strlen($deflated) < $size) {
                $method = 8;
                $stored = $deflated;
            }
        }
        $offset = strlen($this->data);
        $this->data .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0x0800, $method, $this->dosTime, $this->dosDate, $crc, strlen($stored), $size, strlen($name), 0) . $name . $stored;
        $this->entries[] = ['name' => $name, 'crc' => $crc, 'csize' => strlen($stored), 'size' => $size, 'method' => $method, 'offset' => $offset];
    }

    public function output(): string
    {
        $central = '';
        foreach ($this->entries as $e) {
            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0x0800, $e['method'], $this->dosTime, $this->dosDate, $e['crc'], $e['csize'], $e['size'], strlen($e['name']), 0, 0, 0, 0, 0, $e['offset']) . $e['name'];
        }
        $n = count($this->entries);
        return $this->data . $central . pack('VvvvvVVv', 0x06054b50, 0, 0, $n, $n, strlen($central), strlen($this->data), 0);
    }
}
