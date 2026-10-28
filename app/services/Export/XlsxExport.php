<?php
declare(strict_types=1);

namespace App\Services\Export;

use App\Core\ZipWriter;

/**
 * Excel workbook (.xlsx) of an ExportDocument, written as plain OOXML (no library): the standard letterhead at the top (logo picture + organisation, address, e-mail,
 * phones), the title and notes, then the table with a styled header row, frozen headings, filter buttons, zebra rows, sensible column widths and landscape fit-to-width
 * printing. Text goes in as strings (never as formulas); plain numbers stay numbers.
 */
final class XlsxExport
{
    public static function render(ExportDocument $d): string
    {
        $cols = max(1, $d->columns());
        $l = $d->letterhead;
        $rowsXml = [];
        $r = 1;
        $logo = $l['logo'];
        $indent = $logo !== null ? 9 : 0;

        // ---- letterhead rows
        $head = [[$l['org'], 1, 26.0]];
        foreach ([$l['tagline'], $l['address'], $l['contact_line'], $l['reg']] as $line) {
            if ($line !== '') {
                $head[] = [$line, 2, 15.0];
            }
        }
        while (count($head) < 4) {
            $head[] = ['', 2, 15.0];
        }
        foreach ($head as [$text, $style, $ht]) {
            $rowsXml[] = '<row r="' . $r . '" ht="' . $ht . '" customHeight="1">' . ($text !== '' ? self::str($r, 1, $text, $style) : '') . '</row>';
            $r++;
        }
        // gold rule
        $rule = '';
        for ($c = 1; $c <= $cols; $c++) {
            $rule .= '<c r="' . self::ref($r, $c) . '" s="8"/>';
        }
        $rowsXml[] = '<row r="' . $r . '" ht="6" customHeight="1">' . $rule . '</row>';
        $r++;
        // title and notes
        $rowsXml[] = '<row r="' . $r . '" ht="26" customHeight="1">' . self::str($r, 1, $d->title, 3) . '</row>';
        $r++;
        $notes = $d->notes;
        if ($d->generated !== '') {
            array_unshift($notes, $d->generated);
        }
        foreach ($notes as $n) {
            $rowsXml[] = '<row r="' . $r . '">' . self::str($r, 1, $n, 7) . '</row>';
            $r++;
        }
        $rowsXml[] = '<row r="' . $r . '" ht="8" customHeight="1"/>';
        $r++;

        // ---- table
        $headerRow = $r;
        $cells = '';
        foreach ($d->header as $i => $h) {
            $cells .= self::str($r, $i + 1, (string) $h, 4);
        }
        $rowsXml[] = '<row r="' . $r . '" ht="24" customHeight="1">' . $cells . '</row>';
        $r++;
        $widths = [];
        foreach ($d->header as $i => $h) {
            $widths[$i] = mb_strlen((string) $h) + 4;
        }
        foreach ($d->rows as $k => $row) {
            $style = $k % 2 === 1 ? 6 : 5;
            $cells = '';
            for ($c = 0; $c < $cols; $c++) {
                $v = (string) ($row[$c] ?? '');
                if ($k < 400) {
                    $widths[$c] = max($widths[$c] ?? 8, min(60, mb_strlen(strtok($v, "\n") ?: '') + 2));
                }
                $cells .= preg_match('/^(?:0|-?[1-9][0-9]{0,14})(?:\.[0-9]{1,6})?$/', $v) === 1 && !str_starts_with($v, '-0')
                    ? '<c r="' . self::ref($r, $c + 1) . '" s="' . $style . '"><v>' . $v . '</v></c>'
                    : self::str($r, $c + 1, $v, $style);
            }
            $rowsXml[] = '<row r="' . $r . '">' . $cells . '</row>';
            $r++;
        }
        $last = $r - 1;
        if ($d->clause !== '') {
            $r++;
            $rowsXml[] = '<row r="' . $r . '">' . self::str($r, 1, $d->clause, 7) . '</row>';
        }
        $colsXml = '';
        for ($c = 0; $c < $cols; $c++) {
            $colsXml .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . round(max(9, min(60, $widths[$c] ?? 12)) * 1.05, 1) . '" customWidth="1"/>';
        }
        $lastCol = self::col($cols);
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            . '<dimension ref="A1:' . $lastCol . max($d->clause !== '' ? $last + 2 : $last, 1) . '"/>'
            . '<sheetViews><sheetView workbookViewId="0" showGridLines="0"><pane ySplit="' . $headerRow . '" topLeftCell="A' . ($headerRow + 1) . '" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A' . ($headerRow + 1) . '" sqref="A' . ($headerRow + 1) . '"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>' . $colsXml . '</cols>'
            . '<sheetData>' . implode('', $rowsXml) . '</sheetData>'
            . ($d->rows !== [] ? '<autoFilter ref="A' . $headerRow . ':' . $lastCol . $last . '"/>' : '')
            . '<pageMargins left="0.5" right="0.5" top="0.6" bottom="0.6" header="0.3" footer="0.3"/>'
            . '<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>'
            . '<headerFooter><oddFooter>&amp;L&amp;8' . self::esc($l['org']) . '&amp;C&amp;8Confidential: ' . self::esc($l['org']) . ' use only&amp;R&amp;8' . ($d->serial !== '' ? 'Serial ' . self::esc($d->serial) . ' - ' : '') . 'Page &amp;P of &amp;N</oddFooter></headerFooter>'
            . ($logo !== null ? '<drawing r:id="rId1"/>' : '')
            . '</worksheet>';

        $name = preg_replace('/[\[\]:*?\/\\\\]/', ' ', mb_substr($d->title, 0, 31)) ?: 'Export';
        $z = new ZipWriter();
        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . ($logo !== null ? '<Default Extension="' . ($logo['ext'] === 'png' ? 'png" ContentType="image/png"' : 'jpeg" ContentType="image/jpeg"') . '/>' : '')
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . ($logo !== null ? '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>' : '')
            . '</Types>';
        $z->add('[Content_Types].xml', $ct);
        $z->add('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $z->add('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . self::esc(trim($name)) . '" sheetId="1" r:id="rId1"/></sheets>'
            . ($d->rows !== [] ? '<definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">\'' . str_replace("'", "''", trim($name)) . '\'!$A$' . $headerRow . ':$' . $lastCol . '$' . $last . '</definedName><definedName name="_xlnm.Print_Titles" localSheetId="0">\'' . str_replace("'", "''", trim($name)) . '\'!$' . $headerRow . ':$' . $headerRow . '</definedName></definedNames>' : '') . '</workbook>');
        $z->add('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $z->add('xl/styles.xml', self::styles($indent));
        $z->add('xl/worksheets/sheet1.xml', $sheet);
        if ($logo !== null) {
            $px = 64;
            $emu = $px * 9525;
            $z->add('xl/worksheets/_rels/sheet1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/></Relationships>');
            $z->add('xl/drawings/drawing1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
                . '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><xdr:oneCellAnchor><xdr:from><xdr:col>0</xdr:col><xdr:colOff>57150</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>57150</xdr:rowOff></xdr:from><xdr:ext cx="' . $emu . '" cy="' . $emu . '"/>'
                . '<xdr:pic><xdr:nvPicPr><xdr:cNvPr id="2" name="Logo"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr><xdr:blipFill><a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
                . '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $emu . '" cy="' . $emu . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr></xdr:pic><xdr:clientData/></xdr:oneCellAnchor></xdr:wsDr>');
            $z->add('xl/drawings/_rels/drawing1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image1.' . ($logo['ext'] === 'png' ? 'png' : 'jpeg') . '"/></Relationships>');
            $z->add('xl/media/image1.' . ($logo['ext'] === 'png' ? 'png' : 'jpeg'), $logo['bytes']);
        }
        return $z->output();
    }

    private static function styles(int $indent): string
    {
        $ind = $indent > 0 ? ' indent="' . $indent . '"' : '';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="6">'
            . '<font><sz val="10"/><color rgb="FF1D1E20"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FF1D1E20"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><b/><sz val="16"/><color rgb="FF122040"/><name val="Cambria"/><family val="1"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><sz val="9"/><color rgb="FF5B6478"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FF122040"/><name val="Cambria"/><family val="1"/></font>'
            . '</fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF122040"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF7F3EC"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="3"><border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFE4E1DA"/></left><right style="thin"><color rgb="FFE4E1DA"/></right><top style="thin"><color rgb="FFE4E1DA"/></top><bottom style="thin"><color rgb="FFE4E1DA"/></bottom><diagonal/></border>'
            . '<border><left/><right/><top/><bottom style="medium"><color rgb="FFC9973F"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="9">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"' . $ind . '/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"' . $ind . '/></xf>'
            . '<xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0" applyBorder="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private static function str(int $row, int $col, string $text, int $style): string
    {
        return '<c r="' . self::ref($row, $col) . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc($text) . '</t></is></c>';
    }

    private static function esc(string $s): string
    {
        $s = (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $s);
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function col(int $n): string
    {
        $s = '';
        while ($n > 0) {
            $m = ($n - 1) % 26;
            $s = chr(65 + $m) . $s;
            $n = intdiv($n - 1, 26);
        }
        return $s;
    }

    private static function ref(int $row, int $col): string
    {
        return self::col($col) . $row;
    }
}
