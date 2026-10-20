<?php
/**
 * Builds assets/img/brand/card-logo.jpg: the bundled logo of the membership card, the round Lumina seal composited on WHITE (the logo sits on the
 * white side of the card, so no transparency is needed in the PDF) at 384 x 384 px (the card shows it at 64 pt, i.e. 6x for print sharpness).
 * Needs GD:   /c/xampp/php/php.exe -d extension=gd bin/dev/make-card-logo.php
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$src = imagecreatefrompng($root . '/assets/img/brand/logo-512.png');
if ($src === false) {
    fwrite(STDERR, "Cannot read logo-512.png\n");
    exit(1);
}
$size = 384;
$out = imagecreatetruecolor($size, $size);
imagefill($out, 0, 0, (int) imagecolorallocate($out, 255, 255, 255));
imagealphablending($out, true);
imagecopyresampled($out, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));
imagejpeg($out, $root . '/assets/img/brand/card-logo.jpg', 92);
echo 'Wrote assets/img/brand/card-logo.jpg (' . filesize($root . '/assets/img/brand/card-logo.jpg') . " bytes)\n";
