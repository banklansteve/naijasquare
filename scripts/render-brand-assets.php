<?php

/**
 * Raster versions of the square mark and the WhatsApp link card.
 * Colours must match resources/css/app.css.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

$lagoon700 = hexColour('#1B5A61');
$lagoon300 = hexColour('#7EC4CB');
$paper = hexColour('#F5F7F7');
$ink = hexColour('#1A2C2E');
$inkSoft = hexColour('#4A5F61');

function hexColour(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

function allocate(GdImage $image, array $rgb): int
{
    return imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
}

function fillRoundedRect(GdImage $image, int $x, int $y, int $size, int $radius, int $colour): void
{
    $right = $x + $size - 1;
    $bottom = $y + $size - 1;
    $diameter = $radius * 2;

    imagefilledrectangle($image, $x + $radius, $y, $right - $radius, $bottom, $colour);
    imagefilledrectangle($image, $x, $y + $radius, $right, $bottom - $radius, $colour);
    imagefilledellipse($image, $x + $radius, $y + $radius, $diameter, $diameter, $colour);
    imagefilledellipse($image, $right - $radius, $y + $radius, $diameter, $diameter, $colour);
    imagefilledellipse($image, $x + $radius, $bottom - $radius, $diameter, $diameter, $colour);
    imagefilledellipse($image, $right - $radius, $bottom - $radius, $diameter, $diameter, $colour);
}

function drawMark(GdImage $image, int $x, int $y, int $size, array $lagoon700, array $lagoon300, array $paper): void
{
    $tile = (int) round($size * 15 / 64);
    $gap = (int) round($size * 4 / 64);
    $inset = (int) round($size * 15 / 64);
    $tileRadius = max(2, (int) round($size * 4.5 / 64));
    $markRadius = (int) round($size * 16 / 64);

    fillRoundedRect($image, $x, $y, $size, $markRadius, allocate($image, $lagoon700));

    $paperColour = allocate($image, $paper);
    $accent = allocate($image, $lagoon300);

    $positions = [
        [$inset, $inset, $paperColour],
        [$inset + $tile + $gap, $inset, $paperColour],
        [$inset, $inset + $tile + $gap, $paperColour],
        [$inset + $tile + $gap, $inset + $tile + $gap, $accent],
    ];

    foreach ($positions as [$tileX, $tileY, $colour]) {
        fillRoundedRect($image, $x + $tileX, $y + $tileY, $tile, $tileRadius, $colour);
    }
}

function writePng(GdImage $image, string $path): void
{
    $directory = dirname($path);

    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        throw new RuntimeException("Unable to create {$directory}");
    }

    imagepng($image, $path, 6);
    imagedestroy($image);
}

function renderIcon(int $size, bool $maskable, array $lagoon700, array $lagoon300, array $paper): GdImage
{
    $image = imagecreatetruecolor($size, $size);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    if ($maskable) {
        imagefill($image, 0, 0, allocate($image, $lagoon700));
        $mark = (int) round($size * 0.62);
        $offset = (int) round(($size - $mark) / 2);
        drawMark($image, $offset, $offset, $mark, $lagoon700, $lagoon300, $paper);
    } else {
        drawMark($image, 0, 0, $size, $lagoon700, $lagoon300, $paper);
    }

    return $image;
}

function fontPath(string $file): string
{
    $path = 'C:\\Windows\\Fonts\\'.$file;

    if (! is_file($path)) {
        throw new RuntimeException("Missing font {$path}");
    }

    return $path;
}

$icons = $root.'/public/images/icons';
writePng(renderIcon(192, false, $lagoon700, $lagoon300, $paper), $icons.'/icon-192.png');
writePng(renderIcon(512, false, $lagoon700, $lagoon300, $paper), $icons.'/icon-512.png');
writePng(renderIcon(180, false, $lagoon700, $lagoon300, $paper), $icons.'/apple-touch-icon.png');
writePng(renderIcon(512, true, $lagoon700, $lagoon300, $paper), $icons.'/icon-maskable-512.png');

$card = imagecreatetruecolor(1200, 630);
$paperColour = allocate($card, $paper);
$lagoon = allocate($card, $lagoon700);
$inkColour = allocate($card, $ink);
$soft = allocate($card, $inkSoft);
imagefill($card, 0, 0, $paperColour);
imagefilledrectangle($card, 0, 0, 1199, 12, $lagoon);

$bold = fontPath('segoeuib.ttf');
$regular = fontPath('segoeui.ttf');

drawMark($card, 96, 92, 88, $lagoon700, $lagoon300, $paper);
imagettftext($card, 36, 0, 204, 150, $inkColour, $bold, 'NaijaSquare');

$headlineSize = 54;
$lines = [
    'Practical help,',
    'from people like you.',
];
$y = 280;

foreach ($lines as $index => $line) {
    $colour = $index === 1 ? $lagoon : $inkColour;
    imagettftext($card, $headlineSize, 0, 96, $y, $colour, $bold, $line);
    $y += 74;
}

imagettftext(
    $card,
    20,
    0,
    96,
    530,
    $soft,
    $regular,
    'A free community for Nigerians living away from home.',
);

writePng($card, $root.'/public/images/og-card.png');

echo "Brand assets rendered.\n";
