# Exports: CSV, Excel, Word and PDF

Every admin export goes through `CsvExport::response()`, which hands over to `App\Services\Export\Exporter`. Add `?format=csv|xlsx|docx|pdf` to any export address and the same data comes in that format (`xls` / `excel` and `doc` / `word` are accepted as Excel and Word; anything else is CSV, the default).

| Format | For | Notes |
|---|---|---|
| **CSV** | importing, other programs | plain data, UTF-8 with BOM, formula protection (`CsvExport::cell`); never cut |
| **Excel (.xlsx)** | analysis | styled header row, frozen headings, filter buttons, zebra rows, landscape fit-to-width print, text is stored as text (never as formulas), plain numbers stay numbers |
| **Word (.docx)** | reports, letters | the letterhead is the PAGE HEADER (repeats on every page), the table header row repeats, "Page n of N" in the footer |
| **PDF** | printing, sharing | A4, landscape above five columns, letterhead on the first page, running header after, header row repeats, "Page n of N" |

## The standard header (Excel, Word, PDF)

`Letterhead::get()` reads Settings (Contact tab and branding): logo, organisation name, tagline, address + city/country, e-mail(s), phones, website and registration number. Under it: the title (made from the file name, or `meta['title']`), "Exported by NAME on DATE", the filters taken from the address (page / format / sort are not filters) and the table. At most 10,000 rows go into Excel / Word / PDF; the note under the table says so (the CSV is never cut). The PDF is best up to about twelve columns (the forms and membership exports are wide: use Excel for those).

## Adding or changing an export

* A new export just calls `CsvExport::response($filename, $header, $rows)`: all four formats work at once. Pass `['title' => 'Nice title', 'notes' => ['Extra line']]` as the fourth argument to override the title or add lines.
* A CSV that is meant to be **imported again** (templates) passes `['csv_only' => true]`: it stays CSV whatever the address says (`CalendarTransferController::template`).
* In views use the **Export menu**: `partials('partials/admin/export-menu', ['url' => ..., 'label' => 'Export', 'class' => 'btn--outline'])`. `partials/admin/page-header.php` turns any header action with the `download` icon whose address contains `export` into that menu automatically.

## Code

`App\Core\ZipWriter` (pure-PHP ZIP container for the Office files, no ZipArchive needed), `App\Services\Export\{Exporter, ExportDocument, Letterhead, XlsxExport, DocxExport, PdfExport}` (the PDF uses the membership card's `PdfWriter`). Tests: `bin/selftest-exports.php` (writers, valid XML, ZIP and CRC, formats on real admin addresses, the menu).
