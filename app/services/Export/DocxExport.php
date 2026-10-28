<?php
declare(strict_types=1);

namespace App\Services\Export;

use App\Core\ZipWriter;

/**
 * Word document (.docx) of an ExportDocument, written as plain OOXML (no library). The standard letterhead (logo picture, organisation, tagline, address, e-mail, phones)
 * is the page HEADER, so it repeats on every page; the footer has the organisation and "Page n of N". The body has the title, the notes and the table (navy header row that
 * repeats on every page, zebra rows). A4, landscape when the table has more than five columns.
 */
final class DocxExport
{
    private const W = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"';

    public static function render(ExportDocument $d): string
    {
        $cols = max(1, $d->columns());
        $landscape = $cols > 5;
        $pw = $landscape ? 16838 : 11906;
        $ph = $landscape ? 11906 : 16838;
        $margin = 720;
        $usable = $pw - 2 * $margin;
        $l = $d->letterhead;
        $logo = $l['logo'];
        $imgExt = $logo !== null ? ($logo['ext'] === 'png' ? 'png' : 'jpeg') : '';

        // ---- column widths (twips) from the content
        $weights = [];
        for ($c = 0; $c < $cols; $c++) {
            $w = max(6, min(14, mb_strlen((string) ($d->header[$c] ?? ''))));
            foreach (array_slice($d->rows, 0, 300) as $r) {
                $w = max($w, min(38, mb_strlen(strtok((string) ($r[$c] ?? ''), "\n") ?: '')));
            }
            $weights[$c] = $w + 2;
        }
        $sum = array_sum($weights);
        $grid = '';
        $widths = [];
        foreach ($weights as $c => $wt) {
            $widths[$c] = (int) floor($usable * $wt / $sum);
            $grid .= '<w:gridCol w:w="' . $widths[$c] . '"/>';
        }

        $fs = $cols > 10 ? 14 : ($cols > 7 ? 16 : 18);          // half-points
        $border = '<w:top w:val="single" w:sz="4" w:space="0" w:color="E4E1DA"/><w:left w:val="single" w:sz="4" w:space="0" w:color="E4E1DA"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="E4E1DA"/><w:right w:val="single" w:sz="4" w:space="0" w:color="E4E1DA"/>';

        // ---- body
        $body = '<w:p><w:pPr><w:spacing w:before="120" w:after="60"/></w:pPr>' . self::run($d->title, 'Cambria', 32, '122040', true) . '</w:p>';
        $notes = $d->notes;
        if ($d->generated !== '') {
            array_unshift($notes, $d->generated);
        }
        foreach ($notes as $n) {
            $body .= '<w:p><w:pPr><w:spacing w:before="0" w:after="20"/></w:pPr>' . self::run($n, 'Calibri', 17, '5B6478', false) . '</w:p>';
        }
        $body .= '<w:p><w:pPr><w:spacing w:before="0" w:after="60"/></w:pPr></w:p>';
        $tbl = '<w:tbl><w:tblPr><w:tblW w:w="' . $usable . '" w:type="dxa"/><w:tblBorders>' . str_replace(['w:top', 'w:left', 'w:bottom', 'w:right'], ['w:top', 'w:left', 'w:bottom', 'w:right'], $border) . '</w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="40" w:type="dxa"/><w:left w:w="70" w:type="dxa"/><w:bottom w:w="40" w:type="dxa"/><w:right w:w="70" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>' . $grid . '</w:tblGrid>';
        $tbl .= '<w:tr><w:trPr><w:cantSplit/><w:tblHeader/></w:trPr>';
        foreach ($d->header as $c => $h) {
            $tbl .= '<w:tc><w:tcPr><w:tcW w:w="' . $widths[$c] . '" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="122040"/></w:tcPr><w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr>' . self::run((string) $h, 'Calibri', $fs, 'FFFFFF', true) . '</w:p></w:tc>';
        }
        $tbl .= '</w:tr>';
        foreach ($d->rows as $k => $row) {
            $tbl .= '<w:tr><w:trPr><w:cantSplit/></w:trPr>';
            for ($c = 0; $c < $cols; $c++) {
                $shade = $k % 2 === 1 ? '<w:shd w:val="clear" w:color="auto" w:fill="F7F3EC"/>' : '';
                $tbl .= '<w:tc><w:tcPr><w:tcW w:w="' . $widths[$c] . '" w:type="dxa"/>' . $shade . '</w:tcPr><w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr>' . self::run((string) ($row[$c] ?? ''), 'Calibri', $fs, '1D1E20', false) . '</w:p></w:tc>';
            }
            $tbl .= '</w:tr>';
        }
        $tbl .= '</w:tbl>';
        $body .= $tbl;
        $count = count($d->rows);
        $body .= '<w:p><w:pPr><w:spacing w:before="120" w:after="0"/></w:pPr>' . self::run($d->truncated ? 'Only the first ' . $count . ' of ' . $d->total . ' records are shown. Use the CSV export for the full list.' : $count . ' record' . ($count === 1 ? '' : 's'), 'Calibri', 16, '5B6478', false) . '</w:p>';
        $body .= '<w:sectPr><w:headerReference w:type="default" r:id="rId1"/><w:footerReference w:type="default" r:id="rId2"/><w:pgSz w:w="' . $pw . '" w:h="' . $ph . '"' . ($landscape ? ' w:orient="landscape"' : '') . '/><w:pgMar w:top="1900" w:right="' . $margin . '" w:bottom="900" w:left="' . $margin . '" w:header="500" w:footer="400" w:gutter="0"/></w:sectPr>';
        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<w:document ' . self::W . '><w:body>' . $body . '</w:body></w:document>';

        // ---- header: logo + organisation block
        $lines = '<w:p><w:pPr><w:spacing w:before="0" w:after="20"/></w:pPr>' . self::run($l['org'], 'Cambria', 32, '122040', true) . '</w:p>';
        if ($l['tagline'] !== '') {
            $lines .= '<w:p><w:pPr><w:spacing w:before="0" w:after="10"/></w:pPr>' . self::run($l['tagline'], 'Calibri', 17, '9A6F1C', false) . '</w:p>';
        }
        foreach ([$l['address'], $l['contact_line'], $l['reg']] as $t) {
            if ($t !== '') {
                $lines .= '<w:p><w:pPr><w:spacing w:before="0" w:after="10"/></w:pPr>' . self::run($t, 'Calibri', 17, '5B6478', false) . '</w:p>';
            }
        }
        $emu = 640080;      // 0.7 inch
        $logoCell = $logo !== null
            ? '<w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' . $emu . '" cy="' . $emu . '"/><wp:docPr id="1" name="Logo"/><wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="logo.' . $imgExt . '"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $emu . '" cy="' . $emu . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>'
            : '<w:p/>';
        $c1 = $logo !== null ? 1100 : 10;
        $header = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<w:hdr ' . self::W . '>'
            . '<w:tbl><w:tblPr><w:tblW w:w="' . $usable . '" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblCellMar><w:left w:w="0" w:type="dxa"/><w:right w:w="80" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid><w:gridCol w:w="' . $c1 . '"/><w:gridCol w:w="' . ($usable - $c1) . '"/></w:tblGrid>'
            . '<w:tr><w:tc><w:tcPr><w:tcW w:w="' . $c1 . '" w:type="dxa"/></w:tcPr>' . $logoCell . '</w:tc><w:tc><w:tcPr><w:tcW w:w="' . ($usable - $c1) . '" w:type="dxa"/></w:tcPr>' . $lines . '</w:tc></w:tr></w:tbl>'
            . '<w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="12" w:space="1" w:color="C9973F"/></w:pBdr><w:spacing w:before="40" w:after="0"/></w:pPr></w:p></w:hdr>';

        $footer = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<w:ftr ' . self::W . '><w:p><w:pPr><w:pBdr><w:top w:val="single" w:sz="4" w:space="4" w:color="E4E1DA"/></w:pBdr><w:tabs><w:tab w:val="right" w:pos="' . $usable . '"/></w:tabs></w:pPr>'
            . self::run($l['org'] . ($l['website'] !== '' ? '  |  ' . $l['website'] : ''), 'Calibri', 14, '5B6478', false)
            . '<w:r><w:tab/></w:r>' . self::run('Page ', 'Calibri', 14, '5B6478', false)
            . '<w:fldSimple w:instr=" PAGE "><w:r><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:color w:val="5B6478"/><w:sz w:val="14"/></w:rPr><w:t>1</w:t></w:r></w:fldSimple>'
            . self::run(' of ', 'Calibri', 14, '5B6478', false)
            . '<w:fldSimple w:instr=" NUMPAGES "><w:r><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:color w:val="5B6478"/><w:sz w:val="14"/></w:rPr><w:t>1</w:t></w:r></w:fldSimple></w:p></w:ftr>';

        $z = new ZipWriter();
        $z->add('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
            . ($logo !== null ? '<Default Extension="' . $imgExt . '" ContentType="image/' . $imgExt . '"/>' : '')
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/><Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/></Types>');
        $z->add('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $z->add('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/></Relationships>');
        $z->add('word/document.xml', $document);
        $z->add('word/header1.xml', $header);
        $z->add('word/footer1.xml', $footer);
        if ($logo !== null) {
            $z->add('word/_rels/header1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo.' . $imgExt . '"/></Relationships>');
            $z->add('word/media/logo.' . $imgExt, $logo['bytes']);
        }
        return $z->output();
    }

    /** One run of text: font, size in half-points, colour, bold; line breaks inside the text become Word line breaks. */
    private static function run(string $text, string $font, int $size, string $color, bool $bold): string
    {
        $text = (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);
        $rpr = '<w:rPr><w:rFonts w:ascii="' . $font . '" w:hAnsi="' . $font . '" w:cs="' . $font . '"/>' . ($bold ? '<w:b/>' : '') . '<w:color w:val="' . $color . '"/><w:sz w:val="' . $size . '"/></w:rPr>';
        $parts = preg_split('/\r\n|\r|\n/', $text) ?: [''];
        $out = '';
        foreach ($parts as $i => $part) {
            $out .= '<w:r>' . $rpr . ($i > 0 ? '<w:br/>' : '') . '<w:t xml:space="preserve">' . htmlspecialchars($part, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</w:t></w:r>';
        }
        return $out;
    }
}
