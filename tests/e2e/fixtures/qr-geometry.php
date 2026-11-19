<?php
// php qr-geometry.php <png at 600 dpi> <x0> <y0> <x1> <y1>
// Measures a QR code found by zbarimg: edge length, module size and quiet zone.
ini_set('memory_limit', '1G');
[, $file, $x0, $y0, $x1, $y1] = $argv;
[$x0, $y0, $x1, $y1] = array_map('intval', [$x0, $y0, $x1, $y1]);
$img = imagecreatefrompng($file);
$w = imagesx($img);
$h = imagesy($img);
$dark = function (int $x, int $y) use ($img): bool {
    $c = imagecolorat($img, $x, $y);
    $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
    return (0.299 * $r + 0.587 * $g + 0.114 * $b) < 128;
};

// zbar's polygon is approximate: shrink it to the dark pixels it contains.
$m = 40;
$bx0 = PHP_INT_MAX; $by0 = PHP_INT_MAX; $bx1 = -1; $by1 = -1;
for ($yy = max(0, $y0 - $m); $yy <= min($h - 1, $y1 + $m); $yy += 1) {
    for ($xx = max(0, $x0 - $m); $xx <= min($w - 1, $x1 + $m); $xx += 1) {
        if ($dark($xx, $yy)) {
            $bx0 = min($bx0, $xx); $by0 = min($by0, $yy); $bx1 = max($bx1, $xx); $by1 = max($by1, $yy);
        }
    }
}
[$x0, $y0, $x1, $y1] = [$bx0, $by0, $bx1, $by1];

// Top row of the top-left finder pattern is 7 dark modules.
$y = $y0 + 1;
$x = $x0;
while ($x < $x1 && ! $dark($x, $y)) $x++;
$start = $x;
while ($x < $x1 && $dark($x, $y)) $x++;
$module = ($x - $start) / 7;

// White band around the code, per side, in pixels.
$band = function (callable $isWhiteLine, int $max): int {
    $d = 0;
    while ($d < $max && $isWhiteLine($d + 1)) $d++;
    return $d;
};
$colWhite = function (int $x) use ($dark, $y0, $y1, $w): bool {
    if ($x < 0 || $x >= $w) return false;
    for ($y = $y0; $y <= $y1; $y++) if ($dark($x, $y)) return false;
    return true;
};
$rowWhite = function (int $y) use ($dark, $x0, $x1, $h): bool {
    if ($y < 0 || $y >= $h) return false;
    for ($x = $x0; $x <= $x1; $x++) if ($dark($x, $y)) return false;
    return true;
};
$limit = (int) ceil($module * 10);
$quiet = min(
    $band(fn ($d) => $colWhite($x0 - $d), $limit),
    $band(fn ($d) => $colWhite($x1 + $d), $limit),
    $band(fn ($d) => $rowWhite($y0 - $d), $limit),
    $band(fn ($d) => $rowWhite($y1 + $d), $limit),
);
$mm = fn ($px) => round($px / 600 * 25.4, 2);
echo json_encode([
    'edge_mm' => $mm($x1 - $x0),
    'module_mm' => $mm($module),
    'quiet_modules' => $module > 0 ? round($quiet / $module, 1) : 0,
]);
